<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\SharedBillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharedBillingTest extends TestCase
{
    use RefreshDatabase;

    private SharedBillingService $service;

    private User $owner;

    private Kos $kos;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SharedBillingService;
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
    }

    // ── createSharedTagihans ────────────────────────────────

    public function test_create_shared_tagihans_generates_for_all_occupants(): void
    {
        $kamar = $this->createSharedKamar();
        $kontraks = $this->createOccupants($kamar, 3, 1200000);

        $tagihans = $this->service->createSharedTagihans(
            $kamar,
            'Sewa Bulanan',
            '2026-09-01',
            '2026-09-30',
            '2026-10-05',
        );

        $this->assertCount(3, $tagihans);

        foreach ($tagihans as $tagihan) {
            $this->assertEquals('unpaid', $tagihan->status);
            $this->assertEquals(1200000.0, $tagihan->total_room_bill);
            $this->assertEquals(400000.0, $tagihan->subtotal);
            $this->assertEquals('Sewa Bulanan', $tagihan->bill_type);
        }
    }

    public function test_create_shared_tagihans_total_room_bill_stored(): void
    {
        $kamar = $this->createSharedKamar();
        $this->createOccupants($kamar, 2, 1000000);

        $tagihans = $this->service->createSharedTagihans(
            $kamar,
            'Sewa Bulanan',
            '2026-09-01',
            '2026-09-30',
            '2026-10-05',
        );

        $this->assertCount(2, $tagihans);

        // total_room_bill uses kamar's monthly_price (1200000), not kontrak rental_price
        foreach ($tagihans as $tagihan) {
            $this->assertEquals(1200000.0, $tagihan->total_room_bill);
            $this->assertEquals(600000.0, $tagihan->subtotal);
        }
    }

    public function test_create_shared_tagihans_with_discount_and_penalty(): void
    {
        $kamar = $this->createSharedKamar();
        $this->createOccupants($kamar, 2, 1200000);

        $tagihans = $this->service->createSharedTagihans(
            $kamar,
            'Sewa Bulanan',
            '2026-09-01',
            '2026-09-30',
            '2026-10-05',
            100000, // discount
            50000,  // penalty
        );

        $this->assertCount(2, $tagihans);

        foreach ($tagihans as $tagihan) {
            // subtotal=600000, discount=100000, penalty=50000 → total=550000
            $this->assertEquals(100000.0, $tagihan->discount);
            $this->assertEquals(50000.0, $tagihan->penalty);
            $this->assertEquals(550000.0, $tagihan->total);
        }
    }

    public function test_create_shared_tagihans_duplicate_guard(): void
    {
        $kamar = $this->createSharedKamar();
        $this->createOccupants($kamar, 2, 1200000);

        // First call
        $first = $this->service->createSharedTagihans(
            $kamar,
            'Sewa Bulanan',
            '2026-09-01',
            '2026-09-30',
            '2026-10-05',
        );
        $this->assertCount(2, $first);

        // Second call — should not create duplicates
        $second = $this->service->createSharedTagihans(
            $kamar,
            'Sewa Bulanan',
            '2026-09-01',
            '2026-09-30',
            '2026-10-05',
        );
        $this->assertCount(0, $second);

        // Total tagihans in DB should still be 2
        $this->assertDatabaseCount('tagihans', 2);
    }

    public function test_create_shared_tagihans_empty_occupants(): void
    {
        $kamar = $this->createSharedKamar();

        $tagihans = $this->service->createSharedTagihans(
            $kamar,
            'Sewa Bulanan',
            '2026-09-01',
            '2026-09-30',
            '2026-10-05',
        );

        $this->assertCount(0, $tagihans);
    }

    // ── Weekly compatibility ────────────────────────────────

    public function test_create_shared_tagihans_weekly(): void
    {
        $kamar = $this->createSharedKamar();
        $kamar->update(['weekly_price' => 350000]);
        $kamar->refresh();

        $this->createOccupants($kamar, 2, 350000);

        // Update kontraks to weekly
        Kontrak::where('kamar_id', $kamar->id)->update(['rental_type' => 'weekly']);

        $tagihans = $this->service->createSharedTagihans(
            $kamar,
            'Sewa Mingguan',
            '2026-09-01',
            '2026-09-10', // 10 days → ceil(10/7) = 2 weeks
            '2026-09-12',
        );

        $this->assertCount(2, $tagihans);

        // Total room bill: 2 × 350000 = 700000
        foreach ($tagihans as $tagihan) {
            $this->assertEquals(700000.0, $tagihan->total_room_bill);
            $this->assertEquals(350000.0, $tagihan->subtotal);
        }
    }

    // ── Daily compatibility ─────────────────────────────────

    public function test_create_shared_tagihans_daily(): void
    {
        $kamar = $this->createSharedKamar();
        $kamar->update(['daily_price' => 50000]);
        $kamar->refresh();

        $this->createOccupants($kamar, 3, 50000);

        // Update kontraks to daily
        Kontrak::where('kamar_id', $kamar->id)->update(['rental_type' => 'daily']);

        $tagihans = $this->service->createSharedTagihans(
            $kamar,
            'Sewa Harian',
            '2026-09-01',
            '2026-09-03', // 3 days
            '2026-09-04',
        );

        $this->assertCount(3, $tagihans);

        // Total room bill: 3 × 50000 = 150000
        foreach ($tagihans as $tagihan) {
            $this->assertEquals(150000.0, $tagihan->total_room_bill);
            $this->assertEquals(50000.0, $tagihan->subtotal);
        }
    }

    // ── Personal billing regression ─────────────────────────

    public function test_personal_billing_unchanged(): void
    {
        $kamar = Kamar::factory()->create([
            'billing_mode' => 'personal',
            'monthly_price' => 1200000,
            'daily_price' => 50000,
            'weekly_price' => 350000,
        ]);

        $penghuni = Penghuni::factory()->create([
            'kos_id' => $kamar->kos_id,
            'kamar_id' => $kamar->id,
        ]);

        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kamar->kos_id,
            'kamar_id' => $kamar->id,
            'rental_price' => 1200000,
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
        ]);

        $tagihan = Tagihan::create([
            'bill_number' => 'TB-TEST001',
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $kamar->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'subtotal' => 1200000,
            'discount' => 0,
            'penalty' => 0,
            'total' => 1200000,
            'due_date' => '2026-10-05',
            'status' => 'unpaid',
            'active_billing_key' => Tagihan::billingKey($kontrak->id, '2026-09-01', '2026-09-30'),
            'total_room_bill' => null,
        ]);

        $this->assertNull($tagihan->total_room_bill);
        $this->assertEquals(1200000.0, $tagihan->total);
        $this->assertDatabaseHas('tagihans', [
            'kontrak_id' => $kontrak->id,
            'total' => 1200000,
            'total_room_bill' => null,
        ]);
    }

    // ── Departure: recalculate for new cycle ────────────────

    public function test_recalculate_shares_after_departure(): void
    {
        $kamar = $this->createSharedKamar();
        $kontraks = $this->createOccupants($kamar, 3, 1200000);

        // Create tagihans for October (new cycle)
        $tagihans = $this->service->createSharedTagihans(
            $kamar,
            'Sewa Bulanan',
            '2026-10-01',
            '2026-10-31',
            '2026-11-05',
        );
        $this->assertCount(3, $tagihans);

        // Penghuni C checks out
        $kontraks[2]->update(['status' => 'terminated']);

        // Recalculate for the next cycle (November)
        $updated = $this->service->recalculateSharesForCycle(
            $kamar,
            '2026-11-01',
            '2026-11-30',
        );

        // No tagihans for November yet, so 0 updated
        $this->assertEquals(0, $updated);

        // October tagihans should be unchanged
        foreach ($tagihans as $tagihan) {
            $tagihan->refresh();
            $this->assertEquals(400000.0, $tagihan->subtotal);
        }
    }

    // ── Custom override with balance validation ─────────────

    public function test_create_shared_tagihans_with_custom_shares(): void
    {
        $kamar = $this->createSharedKamar();
        $kontraks = $this->createOccupants($kamar, 3, 1200000);

        // Custom override
        $kontraks[0]->update(['custom_share_amount' => 500000]);
        $kontraks[1]->update(['custom_share_amount' => 400000]);
        // kontraks[2] gets the remainder via balance: 1200000 - 500000 - 400000 = 300000

        $tagihans = $this->service->createSharedTagihans(
            $kamar,
            'Sewa Bulanan',
            '2026-09-01',
            '2026-09-30',
            '2026-10-05',
        );

        $this->assertCount(3, $tagihans);

        $subtotals = collect($tagihans)->pluck('subtotal')->sort()->values()->toArray();
        $this->assertEquals([300000.0, 400000.0, 500000.0], $subtotals);

        $totalSubtotals = array_sum($subtotals);
        $this->assertEqualsWithDelta(1200000.0, $totalSubtotals, 0.01, 'SUM(subtotals) must equal total_room_bill');
    }

    // ── Billing mode on kamar ───────────────────────────────

    public function test_kamar_billing_mode_persisted(): void
    {
        $kamar = Kamar::factory()->create(['billing_mode' => 'shared']);

        $this->assertDatabaseHas('kamar', [
            'id' => $kamar->id,
            'billing_mode' => 'shared',
        ]);
    }

    public function test_kamar_billing_mode_default_personal(): void
    {
        $kamar = Kamar::factory()->create();

        $this->assertDatabaseHas('kamar', [
            'id' => $kamar->id,
            'billing_mode' => 'personal',
        ]);
    }

    // ── Helpers ─────────────────────────────────────────────

    private function createSharedKamar(): Kamar
    {
        return Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'billing_mode' => 'shared',
            'monthly_price' => 1200000,
            'daily_price' => 50000,
            'weekly_price' => 350000,
            'capacity' => 10,
        ]);
    }

    /**
     * @return Kontrak[]
     */
    private function createOccupants(Kamar $kamar, int $count, float $rentalPrice): array
    {
        $kontraks = [];
        for ($i = 0; $i < $count; $i++) {
            $penghuni = Penghuni::factory()->create([
                'kos_id' => $kamar->kos_id,
                'kamar_id' => $kamar->id,
            ]);
            $kontrak = Kontrak::factory()->create([
                'penghuni_id' => $penghuni->id,
                'kos_id' => $kamar->kos_id,
                'kamar_id' => $kamar->id,
                'rental_price' => $rentalPrice,
                'rental_type' => 'monthly',
                'billing_mode' => null,
            ]);
            $kontraks[] = $kontrak;
        }

        return $kontraks;
    }
}
