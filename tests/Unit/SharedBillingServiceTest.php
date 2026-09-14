<?php

namespace Tests\Unit;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Services\SharedBillingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharedBillingServiceTest extends TestCase
{
    use RefreshDatabase;

    private SharedBillingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SharedBillingService;
    }

    // ── calculateUnits ──────────────────────────────────────

    public function test_calculate_units_daily(): void
    {
        $start = Carbon::parse('2026-09-01');
        $end = Carbon::parse('2026-09-03');

        $this->assertEquals(3, $this->service->calculateUnits('daily', $start, $end));
    }

    public function test_calculate_units_daily_single_day(): void
    {
        $start = Carbon::parse('2026-09-01');
        $end = Carbon::parse('2026-09-01');

        $this->assertEquals(1, $this->service->calculateUnits('daily', $start, $end));
    }

    public function test_calculate_units_weekly_exact(): void
    {
        $start = Carbon::parse('2026-09-01');
        $end = Carbon::parse('2026-09-07');

        $this->assertEquals(1, $this->service->calculateUnits('weekly', $start, $end));
    }

    public function test_calculate_units_weekly_partial(): void
    {
        $start = Carbon::parse('2026-09-01');
        $end = Carbon::parse('2026-09-10');

        // 10 days → ceil(10/7) = 2
        $this->assertEquals(2, $this->service->calculateUnits('weekly', $start, $end));
    }

    public function test_calculate_units_monthly_single(): void
    {
        $start = Carbon::parse('2026-09-01');
        $end = Carbon::parse('2026-09-30');

        $this->assertEquals(1, $this->service->calculateUnits('monthly', $start, $end));
    }

    public function test_calculate_units_monthly_cross(): void
    {
        $start = Carbon::parse('2026-09-15');
        $end = Carbon::parse('2026-10-15');

        // September + October = 2 months
        $this->assertEquals(2, $this->service->calculateUnits('monthly', $start, $end));
    }

    // ── calculateTotalRoomBill ──────────────────────────────

    public function test_calculate_total_room_bill_monthly(): void
    {
        $kamar = Kamar::factory()->create([
            'monthly_price' => 1200000,
            'daily_price' => 50000,
            'weekly_price' => 350000,
        ]);

        $start = Carbon::parse('2026-09-01');
        $end = Carbon::parse('2026-09-30');

        $total = $this->service->calculateTotalRoomBill($kamar, 'monthly', $start, $end);

        $this->assertEquals(1200000.0, $total);
    }

    public function test_calculate_total_room_bill_daily(): void
    {
        $kamar = Kamar::factory()->create([
            'daily_price' => 50000,
            'monthly_price' => 1200000,
            'weekly_price' => 350000,
        ]);

        $start = Carbon::parse('2026-09-01');
        $end = Carbon::parse('2026-09-03');

        // 3 days × 50000 = 150000
        $total = $this->service->calculateTotalRoomBill($kamar, 'daily', $start, $end);

        $this->assertEquals(150000.0, $total);
    }

    public function test_calculate_total_room_bill_weekly(): void
    {
        $kamar = Kamar::factory()->create([
            'daily_price' => 50000,
            'monthly_price' => 1200000,
            'weekly_price' => 350000,
        ]);

        $start = Carbon::parse('2026-09-01');
        $end = Carbon::parse('2026-09-10');

        // 10 days → ceil(10/7) = 2 weeks → 2 × 350000 = 700000
        $total = $this->service->calculateTotalRoomBill($kamar, 'weekly', $start, $end);

        $this->assertEquals(700000.0, $total);
    }

    // ── calculateShare — equal split ────────────────────────

    public function test_share_equal_split_3_occupants(): void
    {
        $kamar = $this->createSharedKamar();
        $kontraks = $this->createOccupants($kamar, 3, 1200000);

        $totalRoomBill = 1200000.0;
        $allKontraks = $kamar->activeKontraks()->get();

        $shares = [];
        foreach ($allKontraks as $kontrak) {
            $shares[] = $this->service->calculateShare($kontrak, $totalRoomBill, 3, $allKontraks);
        }

        // 1200000 / 3 = 400000 each
        $this->assertEquals([400000.0, 400000.0, 400000.0], $shares);
        $this->assertEqualsWithDelta($totalRoomBill, array_sum($shares), 0.01);
    }

    public function test_share_equal_split_2_occupants(): void
    {
        $kamar = $this->createSharedKamar();
        $kontraks = $this->createOccupants($kamar, 2, 1000000);

        $totalRoomBill = 1000000.0;
        $allKontraks = $kamar->activeKontraks()->get();

        $shares = [];
        foreach ($allKontraks as $kontrak) {
            $shares[] = $this->service->calculateShare($kontrak, $totalRoomBill, 2, $allKontraks);
        }

        $this->assertEquals([500000.0, 500000.0], $shares);
        $this->assertEqualsWithDelta($totalRoomBill, array_sum($shares), 0.01);
    }

    public function test_share_single_occupant(): void
    {
        $kamar = $this->createSharedKamar();
        $kontraks = $this->createOccupants($kamar, 1, 1500000);

        $totalRoomBill = 1500000.0;
        $allKontraks = $kamar->activeKontraks()->get();

        $shares = [];
        foreach ($allKontraks as $kontrak) {
            $shares[] = $this->service->calculateShare($kontrak, $totalRoomBill, 1, $allKontraks);
        }

        $this->assertEquals([1500000.0], $shares);
        $this->assertEqualsWithDelta($totalRoomBill, array_sum($shares), 0.01);
    }

    // ── Rounding: SUM(shares) must equal total ──────────────

    public function test_share_rounding_100000_div_3(): void
    {
        $kamar = $this->createSharedKamar();
        $kontraks = $this->createOccupants($kamar, 3, 100000);

        $totalRoomBill = 100000.0;
        $allKontraks = $kamar->activeKontraks()->get();

        $shares = [];
        foreach ($allKontraks as $kontrak) {
            $shares[] = $this->service->calculateShare($kontrak, $totalRoomBill, 3, $allKontraks);
        }

        // SUM must equal exactly 100000
        $this->assertEqualsWithDelta($totalRoomBill, array_sum($shares), 0.01, 'SUM(shares) must equal total_room_bill');
    }

    public function test_share_rounding_100000_div_7(): void
    {
        $kamar = $this->createSharedKamar();
        $kontraks = $this->createOccupants($kamar, 7, 100000);

        $totalRoomBill = 100000.0;
        $allKontraks = $kamar->activeKontraks()->get();

        $shares = [];
        foreach ($allKontraks as $kontrak) {
            $shares[] = $this->service->calculateShare($kontrak, $totalRoomBill, 7, $allKontraks);
        }

        // SUM must equal exactly 100000
        $this->assertEqualsWithDelta($totalRoomBill, array_sum($shares), 0.01, 'SUM(shares) must equal total_room_bill');
    }

    public function test_share_rounding_100001_div_3(): void
    {
        $kamar = $this->createSharedKamar();
        $kontraks = $this->createOccupants($kamar, 3, 100001);

        $totalRoomBill = 100001.0;
        $allKontraks = $kamar->activeKontraks()->get();

        $shares = [];
        foreach ($allKontraks as $kontrak) {
            $shares[] = $this->service->calculateShare($kontrak, $totalRoomBill, 3, $allKontraks);
        }

        // SUM must equal exactly 100001
        $this->assertEqualsWithDelta($totalRoomBill, array_sum($shares), 0.01, 'SUM(shares) must equal total_room_bill');
    }

    // ── Custom override ─────────────────────────────────────

    public function test_share_custom_override(): void
    {
        $kamar = $this->createSharedKamar();
        $kontraks = $this->createOccupants($kamar, 3, 1200000);

        // Set custom share for first kontrak
        $kontraks[0]->update(['custom_share_amount' => 500000]);
        $kontraks[0]->refresh();

        $totalRoomBill = 1200000.0;
        $allKontraks = $kamar->activeKontraks()->get();

        $shareA = $this->service->calculateShare($kontraks[0], $totalRoomBill, 3, $allKontraks);
        $this->assertEquals(500000.0, $shareA);
    }

    public function test_validate_custom_shares_valid(): void
    {
        $shares = [
            ['kontrak_id' => 1, 'share' => 500000],
            ['kontrak_id' => 2, 'share' => 400000],
            ['kontrak_id' => 3, 'share' => 300000],
        ];

        $this->assertTrue($this->service->validateCustomShares(1200000.0, $shares));
    }

    public function test_validate_custom_shares_invalid(): void
    {
        $shares = [
            ['kontrak_id' => 1, 'share' => 500000],
            ['kontrak_id' => 2, 'share' => 400000],
            ['kontrak_id' => 3, 'share' => 400000],
        ];

        // 500k + 400k + 400k = 1.3m ≠ 1.2m
        $this->assertFalse($this->service->validateCustomShares(1200000.0, $shares));
    }

    // ── calculateAllShares with balance ─────────────────────

    public function test_calculate_all_shares_balances(): void
    {
        $kamar = $this->createSharedKamar();
        $this->createOccupants($kamar, 3, 1200000);

        $totalRoomBill = 1200000.0;
        $allShares = $this->service->calculateAllShares($kamar, $totalRoomBill);

        $sum = 0;
        foreach ($allShares as $s) {
            $sum += $s['share'];
        }

        $this->assertEqualsWithDelta($totalRoomBill, $sum, 0.01, 'calculateAllShares must balance');
    }

    public function test_calculate_all_shares_with_custom_balances(): void
    {
        $kamar = $this->createSharedKamar();
        $kontraks = $this->createOccupants($kamar, 3, 1200000);

        // Custom override for first
        $kontraks[0]->update(['custom_share_amount' => 500000]);

        $totalRoomBill = 1200000.0;
        $allShares = $this->service->calculateAllShares($kamar, $totalRoomBill);

        $sum = 0;
        foreach ($allShares as $s) {
            $sum += $s['share'];
        }

        $this->assertEqualsWithDelta($totalRoomBill, $sum, 0.01, 'calculateAllShares with custom must balance');
    }

    public function test_calculate_all_shares_zero_occupants(): void
    {
        $kamar = $this->createSharedKamar();
        $allShares = $this->service->calculateAllShares($kamar, 1200000.0);

        $this->assertEmpty($allShares);
    }

    // ── Model helpers ───────────────────────────────────────

    public function test_kamar_default_is_personal(): void
    {
        $kamar = Kamar::factory()->create(['billing_mode' => 'personal']);

        $this->assertTrue($kamar->isPersonal());
        $this->assertFalse($kamar->isShared());
    }

    public function test_kamar_shared(): void
    {
        $kamar = Kamar::factory()->create(['billing_mode' => 'shared']);

        $this->assertTrue($kamar->isShared());
        $this->assertFalse($kamar->isPersonal());
    }

    public function test_kamar_billing_mode_default(): void
    {
        $kamar = Kamar::factory()->create();

        // Default should be 'personal'
        $this->assertEquals('personal', $kamar->billing_mode);
    }

    public function test_kontrak_effective_billing_mode_fallback_to_kamar(): void
    {
        $kos = Kos::factory()->create();
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'billing_mode' => 'shared']);
        $penghuni = Penghuni::factory()->create(['kos_id' => $kos->id, 'kamar_id' => $kamar->id]);
        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'billing_mode' => null,
        ]);

        $this->assertEquals('shared', $kontrak->effectiveBillingMode());
    }

    public function test_kontrak_effective_billing_mode_override(): void
    {
        $kos = Kos::factory()->create();
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'billing_mode' => 'shared']);
        $penghuni = Penghuni::factory()->create(['kos_id' => $kos->id, 'kamar_id' => $kamar->id]);
        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'billing_mode' => 'personal',
        ]);

        $this->assertEquals('personal', $kontrak->effectiveBillingMode());
    }

    public function test_kontrak_is_shared(): void
    {
        $kos = Kos::factory()->create();
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'billing_mode' => 'shared']);
        $penghuni = Penghuni::factory()->create(['kos_id' => $kos->id, 'kamar_id' => $kamar->id]);
        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'billing_mode' => null,
        ]);

        $this->assertTrue($kontrak->isShared());
    }

    // ── Helpers ─────────────────────────────────────────────

    private function createSharedKamar(): Kamar
    {
        return Kamar::factory()->create([
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
