<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\ManagementAgreement;
use App\Models\Payout;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\PropertyOwner;
use App\Models\PropertyOwnerBankAccount;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\PropertyOwnerPayoutService;
use App\Services\SettlementReportService;
use App\Services\SettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SettlementRefundFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Kos $kos;

    private Kamar $kamar;

    private Penghuni $penghuni;

    private Kontrak $kontrak;

    private Tagihan $tagihan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'pencari']);

        $propertyOwner = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $propertyOwner->id]);

        $this->kos = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'active',
            'property_owner_id' => $propertyOwner->id,
        ]);

        ManagementAgreement::factory()->create([
            'kos_id' => $this->kos->id,
            'property_owner_id' => $propertyOwner->id,
            'commission_type' => 'percent',
            'commission_value' => 10,
            'effective_from' => '2026-01-01',
        ]);

        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);
        $this->penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);
        $this->kontrak = Kontrak::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);
        $this->tagihan = Tagihan::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kontrak_id' => $this->kontrak->id,
            'kamar_id' => $this->kamar->id,
            'subtotal' => 4000000,
            'penalty' => 0,
            'total' => 4000000,
        ]);
    }

    private function makePayment(float $amount, string $refundStatus, string $paymentDate = '2026-09-10'): Pembayaran
    {
        return Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => $amount,
            'rent_amount' => $amount,
            'penalty_amount' => 0,
            'payment_date' => $paymentDate,
            'verification_status' => Pembayaran::STATUS_APPROVED,
            'refund_status' => $refundStatus,
        ]);
    }

    public static function excludedRefundStatuses(): array
    {
        return [
            'requested' => [Pembayaran::REFUND_REQUESTED],
            'approved' => [Pembayaran::REFUND_APPROVED],
            'completed' => [Pembayaran::REFUND_COMPLETED],
        ];
    }

    #[DataProvider('excludedRefundStatuses')]
    public function test_payment_in_refund_flow_is_excluded_from_new_settlement(string $refundStatus): void
    {
        $this->makePayment(2000000, Pembayaran::REFUND_NONE);
        $this->makePayment(1000000, $refundStatus);

        $settlement = app(SettlementService::class)
            ->resolveSettlementItems($this->kos, '2026-09-01', '2026-09-30');

        $this->assertSame(1, $settlement['pembayaran_count']);
        $this->assertSame(2000000.0, $settlement['gross_total']);
        $this->assertSame(200000.0, $settlement['commission_total']);
        $this->assertSame(1800000.0, $settlement['net_total']);
    }

    public function test_refund_rejected_payment_remains_eligible(): void
    {
        $this->makePayment(2000000, Pembayaran::REFUND_REJECTED);

        $settlement = app(SettlementService::class)
            ->resolveSettlementItems($this->kos, '2026-09-01', '2026-09-30');

        $this->assertSame(1, $settlement['pembayaran_count']);
        $this->assertSame(2000000.0, $settlement['gross_total']);
    }

    public function test_non_refundable_down_payment_remains_eligible_after_booking_cancelled(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => Booking::STATUS_CANCELLED,
            'price' => 1500000,
            'down_payment_amount' => 100000,
            'start_date' => '2026-09-15',
            'end_date' => '2026-10-15',
            'rental_type' => 'monthly',
        ]);

        Pembayaran::factory()->create([
            'tagihan_id' => null,
            'booking_id' => $booking->id,
            'penghuni_id' => null,
            'amount' => 100000,
            'rent_amount' => null,
            'penalty_amount' => null,
            'payment_date' => '2026-09-10',
            'verification_status' => Pembayaran::STATUS_APPROVED,
            'refund_status' => Pembayaran::REFUND_NONE,
        ]);

        $settlement = app(SettlementService::class)
            ->resolveSettlementItems($this->kos, '2026-09-01', '2026-09-30');

        $this->assertSame(1, $settlement['pembayaran_count']);
        $this->assertSame(100000.0, $settlement['gross_total']);
        $this->assertSame(10000.0, $settlement['commission_total']);
        $this->assertSame(90000.0, $settlement['net_total']);
    }

    public function test_payout_creation_only_counts_eligible_payments(): void
    {
        $this->makePayment(2000000, Pembayaran::REFUND_NONE);
        $this->makePayment(1000000, Pembayaran::REFUND_REQUESTED, '2026-09-12');

        $result = app(PropertyOwnerPayoutService::class)->createForKos($this->owner, [
            'kos_id' => $this->kos->id,
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
        ]);

        $this->assertTrue($result['ok']);
        $this->assertSame(1800000.0, (float) $result['payout']->amount);
        $this->assertSame(1, $result['payout']->items()->count());
    }

    public function test_payout_is_rejected_when_all_payments_are_in_refund_flow(): void
    {
        $this->makePayment(2000000, Pembayaran::REFUND_REQUESTED);

        $result = app(PropertyOwnerPayoutService::class)->createForKos($this->owner, [
            'kos_id' => $this->kos->id,
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
        ]);

        $this->assertFalse($result['ok']);
        $this->assertSame('no_eligible_income', $result['error']);
        $this->assertSame(0, Payout::where('kos_id', $this->kos->id)->count());
    }

    public function test_report_received_excludes_payments_in_refund_flow(): void
    {
        $this->makePayment(2000000, Pembayaran::REFUND_NONE);
        $this->makePayment(1000000, Pembayaran::REFUND_APPROVED, '2026-09-12');

        $report = app(SettlementReportService::class)->forOwner($this->owner);

        $this->assertSame(2000000.0, (float) $report['received']);
        $this->assertSame(2000000.0, array_sum(array_column($report['per_kos'], 'received')));
        $this->assertSame(
            2000000.0,
            app(PropertyOwnerPayoutService::class)->resolveGross($this->kos, '2026-09-01', '2026-09-30')
        );
    }
}
