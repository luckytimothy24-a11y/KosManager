<?php

namespace Tests\Feature\Characterization;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\ManagementAgreement;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\PropertyOwner;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\BillingService;
use App\Services\SettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * FASE 0 — KARAKTERISASI PERHITUNGAN UANG.
 *
 * Baseline masih memakai decimal/float 2 angka di belakang koma.
 * Ini menjadi titik awal migrasi ke integer rupiah.
 */
class MoneyMathCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Kos $kos;

    private Kamar $kamar;

    private Penghuni $penghuni;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'pencari']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'occupied',
            'monthly_price' => 1500000,
        ]);
        $this->penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);
    }

    public function test_money_attributes_are_cast_to_two_decimal_strings(): void
    {
        // karakterisasi, bukan perilaku yang diinginkan.
        $this->assertSame('1500000.00', $this->kamar->monthly_price);

        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kontrak_id' => $this->makeKontrak()->id,
            'kamar_id' => $this->kamar->id,
            'subtotal' => 1000000,
            'discount' => 100000,
            'penalty' => 50000,
            'total' => 950000,
        ]);

        $this->assertSame('1000000.00', $tagihan->subtotal);
        $this->assertSame('950000.00', $tagihan->total);
    }

    public function test_down_payment_is_100000_and_capped_forfeit_at_first_bill(): void
    {
        $this->kamar->update(['monthly_price' => 50000]);

        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'rental_type' => 'monthly',
            'rental_price' => 50000,
            'down_payment_amount' => 100000,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->addMonths(3)->startOfMonth()->toDateString(),
            'status' => 'active',
        ]);

        $result = app(BillingService::class)->generateFirstBill($kontrak);
        $this->assertSame('generated', $result);

        $tagihan = Tagihan::where('kontrak_id', $kontrak->id)->firstOrFail();
        $this->assertEquals(50000, (float) $tagihan->subtotal);
        $this->assertEquals(50000, (float) $tagihan->down_payment_discount);
        $this->assertEquals(0, (float) $tagihan->total);
        $this->assertSame('paid', $tagihan->status);

        // Sisa DP 50.000 hangus; bukan refund dan bukan saldo.
        $this->assertEquals(0, (float) $kontrak->fresh()->down_payment_amount);
    }

    public function test_overdue_penalty_is_fixed_50000_and_added_once(): void
    {
        $this->assertSame(50000, (int) config('kosmanager.late_fee.fixed_amount'));

        $table = Tagihan::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kontrak_id' => $this->makeKontrak()->id,
            'kamar_id' => $this->kamar->id,
            'subtotal' => 1000000,
            'discount' => 0,
            'penalty' => 0,
            'total' => 1000000,
            'due_date' => now()->subDay()->toDateString(),
            'status' => 'unpaid',
        ]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();
        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $table->refresh();
        $this->assertSame('overdue', $table->status);
        $this->assertEquals(50000, (float) $table->penalty);
        $this->assertEquals(1050000, (float) $table->total);
    }

    #[Group('alur-dana-2-tahap')]
    public function test_percent_commission_rounds_to_two_decimals(): void
    {
        $propertyOwner = PropertyOwner::factory()->create();
        $this->kos->update(['property_owner_id' => $propertyOwner->id]);

        ManagementAgreement::factory()->create([
            'kos_id' => $this->kos->id,
            'property_owner_id' => $propertyOwner->id,
            'commission_type' => 'percent',
            'commission_value' => 33.33,
            'effective_from' => now()->subYear()->toDateString(),
            'effective_to' => null,
        ]);

        $pembayaran = Pembayaran::factory()->make([
            'amount' => 999999,
            'rent_amount' => 999999,
            'penalty_amount' => 0,
            'payment_date' => now()->toDateString(),
            'verification_status' => 'approved',
        ]);

        $resolved = app(SettlementService::class)->resolveCommissionForPembayaran($this->kos, $pembayaran);

        $this->assertNotNull($resolved);
        $this->assertEquals(333299.67, $resolved['commission_amount']);
        $this->assertEquals(666699.33, $resolved['net_amount']);
    }

    private function makeKontrak(): Kontrak
    {
        return Kontrak::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'rental_type' => 'monthly',
            'rental_price' => 1500000,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonths(5)->toDateString(),
            'status' => 'active',
        ]);
    }
}
