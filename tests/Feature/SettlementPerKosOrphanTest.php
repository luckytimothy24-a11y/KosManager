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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettlementPerKosOrphanTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $superAdmin;

    private Kos $kosA;

    private Kos $kosB;

    private PropertyOwner $poA;

    private PropertyOwner $poB;

    private Penghuni $penghuniA;

    private Penghuni $penghuniB;

    private Tagihan $tagihanA;

    private Tagihan $tagihanB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $tenantA = User::factory()->create(['role' => 'tenant']);
        $tenantB = User::factory()->create(['role' => 'tenant']);
        $this->poA = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $this->poA->id]);
        $this->poB = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $this->poB->id]);
        $this->kosA = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'property_owner_id' => $this->poA->id]);
        $this->kosB = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'property_owner_id' => $this->poB->id]);
        $kamarA = Kamar::factory()->create(['kos_id' => $this->kosA->id]);
        $kamarB = Kamar::factory()->create(['kos_id' => $this->kosB->id]);
        $this->penghuniA = Penghuni::factory()->create(['user_id' => $tenantA->id, 'kos_id' => $this->kosA->id, 'kamar_id' => $kamarA->id, 'status' => 'active']);
        $this->penghuniB = Penghuni::factory()->create(['user_id' => $tenantB->id, 'kos_id' => $this->kosB->id, 'kamar_id' => $kamarB->id, 'status' => 'active']);
        $kontrakA = Kontrak::factory()->create(['penghuni_id' => $this->penghuniA->id, 'kos_id' => $this->kosA->id, 'kamar_id' => $kamarA->id, 'status' => 'active']);
        $kontrakB = Kontrak::factory()->create(['penghuni_id' => $this->penghuniB->id, 'kos_id' => $this->kosB->id, 'kamar_id' => $kamarB->id, 'status' => 'active']);
        $this->tagihanA = Tagihan::factory()->create(['penghuni_id' => $this->penghuniA->id, 'kontrak_id' => $kontrakA->id, 'kamar_id' => $kamarA->id, 'subtotal' => 2000000, 'penalty' => 0, 'total' => 2000000]);
        $this->tagihanB = Tagihan::factory()->create(['penghuni_id' => $this->penghuniB->id, 'kontrak_id' => $kontrakB->id, 'kamar_id' => $kamarB->id, 'subtotal' => 2000000, 'penalty' => 0, 'total' => 2000000]);
        ManagementAgreement::factory()->create(['kos_id' => $this->kosA->id, 'property_owner_id' => $this->poA->id, 'commission_type' => 'percent', 'commission_value' => 10, 'effective_from' => '2026-01-01']);
        ManagementAgreement::factory()->create(['kos_id' => $this->kosB->id, 'property_owner_id' => $this->poB->id, 'commission_type' => 'percent', 'commission_value' => 10, 'effective_from' => '2026-01-01']);
    }

    private function payPenghuni(Penghuni $penghuni, Tagihan $tagihan, int $rent, string $date): Pembayaran
    {
        return Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id, 'penghuni_id' => $penghuni->id, 'booking_id' => null,
            'amount' => $rent, 'rent_amount' => $rent, 'penalty_amount' => 0,
            'payment_date' => $date, 'verification_status' => 'approved',
        ]);
    }

    private function createPayout(Kos $kos, string $start, string $end): Payout
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $res = $svc->createForKos($this->owner, ['kos_id' => $kos->id, 'period_start' => $start, 'period_end' => $end]);
        $this->assertTrue($res['ok'], json_encode($res));

        return $res['payout'];
    }

    private function report(): array
    {
        return app(SettlementReportService::class)->forOwner($this->owner);
    }

    private function perKosById(array $report, int $kosId): array
    {
        foreach ($report['per_kos'] as $row) {
            if ((int) $row['kos']->id === $kosId) {
                return $row;
            }
        }
        $this->fail("per_kos missing kos_id {$kosId}");
    }

    public function test_orphan_adjustment_mapped_to_correct_kos_and_invariant_holds(): void
    {
        $this->payPenghuni($this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $this->payPenghuni($this->penghuniB, $this->tagihanB, 1000000, '2026-09-10');
        $this->createPayout($this->kosA, '2026-09-01', '2026-09-30');
        $this->createPayout($this->kosB, '2026-09-01', '2026-09-30');
        $orphanA = $this->payPenghuni($this->penghuniA, $this->tagihanA, 1000000, '2026-09-20');
        $orphanB = $this->payPenghuni($this->penghuniB, $this->tagihanB, 1000000, '2026-09-20');

        $svc = app(PropertyOwnerPayoutService::class);
        $rA = $svc->createAdjustment($this->superAdmin, ['pembayaran_id' => $orphanA->id, 'type' => 'refund', 'amount' => 200000, 'reason' => 'orphan kos A']);
        $this->assertTrue($rA['ok']);
        $this->assertNull($rA['adjustment']->payout_id);
        $rB = $svc->createAdjustment($this->superAdmin, ['pembayaran_id' => $orphanB->id, 'type' => 'refund', 'amount' => 300000, 'reason' => 'orphan kos B']);
        $this->assertTrue($rB['ok']);

        $report = $this->report();
        $rowA = $this->perKosById($report, $this->kosA->id);
        $rowB = $this->perKosById($report, $this->kosB->id);

        $this->assertEqualsWithDelta(-180000.0, $rowA['adjustments_net'], 0.005);
        $this->assertEqualsWithDelta(-270000.0, $rowB['adjustments_net'], 0.005);
        $this->assertEqualsWithDelta($report['adjustments_net'], $rowA['adjustments_net'] + $rowB['adjustments_net'], 0.005);
        $this->assertEqualsWithDelta($report['net_obligation'] + $report['adjustments_net'] - $report['payout_verified'], $report['remaining_raw'], 0.005);
        $this->assertEqualsWithDelta($rowA['net'] + $rowA['adjustments_net'] - $rowA['verified'], $rowA['remaining_raw'], 0.005);
        $this->assertEqualsWithDelta($rowB['net'] + $rowB['adjustments_net'] - $rowB['verified'], $rowB['remaining_raw'], 0.005);
    }

    public function test_orphan_booking_path_mapped_correctly(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kosA->id]);
        $booking = Booking::factory()->create(['kos_id' => $this->kosA->id, 'kamar_id' => $kamar->id, 'user_id' => User::factory()->create(['role' => 'tenant'])->id, 'status' => 'pending']);
        $payBooking = Pembayaran::factory()->create([
            'booking_id' => $booking->id, 'tagihan_id' => null, 'penghuni_id' => null,
            'amount' => 1200000, 'rent_amount' => 1200000, 'penalty_amount' => 0,
            'payment_date' => '2026-09-15', 'verification_status' => 'approved',
        ]);
        $this->payPenghuni($this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $this->payPenghuni($this->penghuniB, $this->tagihanB, 1000000, '2026-09-10');
        $this->createPayout($this->kosA, '2026-09-01', '2026-09-30');
        $this->createPayout($this->kosB, '2026-09-01', '2026-09-30');

        $svc = app(PropertyOwnerPayoutService::class);
        $r = $svc->createAdjustment($this->superAdmin, ['pembayaran_id' => $payBooking->id, 'type' => 'refund', 'amount' => 120000, 'reason' => 'orphan booking']);
        $this->assertTrue($r['ok']);

        $report = $this->report();
        $rowA = $this->perKosById($report, $this->kosA->id);
        $rowB = $this->perKosById($report, $this->kosB->id);
        $this->assertEqualsWithDelta(-108000.0, $rowA['adjustments_net'], 0.005);
        $this->assertEqualsWithDelta(0.0, $rowB['adjustments_net'], 0.005);
        $this->assertEqualsWithDelta($report['adjustments_net'], $rowA['adjustments_net'] + $rowB['adjustments_net'], 0.005);
    }

    public function test_orphan_priority_penghuni_over_booking(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kosA->id]);
        $booking = Booking::factory()->create(['kos_id' => $this->kosB->id, 'kamar_id' => $kamar->id, 'user_id' => User::factory()->create(['role' => 'tenant'])->id, 'status' => 'pending']);
        $payBoth = Pembayaran::factory()->create([
            'booking_id' => $booking->id, 'penghuni_id' => $this->penghuniA->id, 'tagihan_id' => $this->tagihanA->id,
            'amount' => 1000000, 'rent_amount' => 1000000, 'penalty_amount' => 0,
            'payment_date' => '2026-09-15', 'verification_status' => 'approved',
        ]);
        $this->payPenghuni($this->penghuniB, $this->tagihanB, 1000000, '2026-09-10');
        $this->createPayout($this->kosA, '2026-09-01', '2026-09-30');
        $this->createPayout($this->kosB, '2026-09-01', '2026-09-30');
        $svc = app(PropertyOwnerPayoutService::class);
        $r = $svc->createAdjustment($this->superAdmin, ['pembayaran_id' => $payBoth->id, 'type' => 'refund', 'amount' => 100000, 'reason' => 'orphan both']);
        $this->assertTrue($r['ok']);
        $report = $this->report();
        $rowA = $this->perKosById($report, $this->kosA->id);
        $rowB = $this->perKosById($report, $this->kosB->id);
        $this->assertEqualsWithDelta(-90000.0, $rowA['adjustments_net'], 0.005);
        $this->assertEqualsWithDelta(0.0, $rowB['adjustments_net'], 0.005);
    }

    public function test_global_invariant_with_mixed_linked_and_orphan(): void
    {
        $payA = $this->payPenghuni($this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $payB = $this->payPenghuni($this->penghuniB, $this->tagihanB, 1000000, '2026-09-10');
        $payoutA = $this->createPayout($this->kosA, '2026-09-01', '2026-09-30');
        $this->createPayout($this->kosB, '2026-09-01', '2026-09-30');
        $svc = app(PropertyOwnerPayoutService::class);
        $svc->createAdjustment($this->superAdmin, ['pembayaran_id' => $payA->id, 'payout_id' => $payoutA->id, 'type' => 'refund', 'amount' => 100000, 'reason' => 'linked kos A']);
        $svc->createAdjustment($this->superAdmin, ['pembayaran_id' => $payB->id, 'type' => 'refund', 'amount' => 200000, 'reason' => 'orphan kos B']);
        $report = $this->report();
        $sumPerKos = array_sum(array_map(fn ($r) => (float) $r['adjustments_net'], $report['per_kos']));
        $this->assertEqualsWithDelta($report['adjustments_net'], $sumPerKos, 0.005);
        $rowA = $this->perKosById($report, $this->kosA->id);
        $rowB = $this->perKosById($report, $this->kosB->id);
        $this->assertEqualsWithDelta(-90000.0, $rowA['adjustments_net'], 0.005);
        $this->assertEqualsWithDelta(-180000.0, $rowB['adjustments_net'], 0.005);
    }

    public function test_owner_isolation_orphan_not_leaked(): void
    {
        $otherOwner = User::factory()->create(['role' => 'owner']);
        $poOther = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $poOther->id]);
        $kosOther = Kos::factory()->create(['owner_id' => $otherOwner->id, 'property_owner_id' => $poOther->id, 'status' => 'active']);
        $kamarOther = Kamar::factory()->create(['kos_id' => $kosOther->id]);
        $tenantOther = User::factory()->create(['role' => 'tenant']);
        $penghuniOther = Penghuni::factory()->create(['user_id' => $tenantOther->id, 'kos_id' => $kosOther->id, 'kamar_id' => $kamarOther->id, 'status' => 'active']);
        $kontrakOther = Kontrak::factory()->create(['penghuni_id' => $penghuniOther->id, 'kos_id' => $kosOther->id, 'kamar_id' => $kamarOther->id, 'status' => 'active']);
        $tagihanOther = Tagihan::factory()->create(['penghuni_id' => $penghuniOther->id, 'kontrak_id' => $kontrakOther->id, 'kamar_id' => $kamarOther->id]);
        ManagementAgreement::factory()->create(['kos_id' => $kosOther->id, 'property_owner_id' => $poOther->id, 'commission_type' => 'percent', 'commission_value' => 10, 'effective_from' => '2026-01-01']);
        $payOther = Pembayaran::factory()->create(['tagihan_id' => $tagihanOther->id, 'penghuni_id' => $penghuniOther->id, 'amount' => 1000000, 'rent_amount' => 1000000, 'penalty_amount' => 0, 'payment_date' => '2026-09-10', 'verification_status' => 'approved']);

        $this->payPenghuni($this->penghuniA, $this->tagihanA, 1000000, '2026-09-10');
        $this->payPenghuni($this->penghuniB, $this->tagihanB, 1000000, '2026-09-10');
        $this->createPayout($this->kosA, '2026-09-01', '2026-09-30');
        $this->createPayout($this->kosB, '2026-09-01', '2026-09-30');
        $orphanA = $this->payPenghuni($this->penghuniA, $this->tagihanA, 1000000, '2026-09-20');
        $svc = app(PropertyOwnerPayoutService::class);
        $svc->createAdjustment($this->superAdmin, ['pembayaran_id' => $payOther->id, 'type' => 'refund', 'amount' => 500000, 'reason' => 'orphan other owner']);
        $svc->createAdjustment($this->superAdmin, ['pembayaran_id' => $orphanA->id, 'type' => 'refund', 'amount' => 100000, 'reason' => 'orphan own']);

        $report = $this->report();
        $this->assertEqualsWithDelta(-90000.0, $report['adjustments_net'], 0.005);
        $rowA = $this->perKosById($report, $this->kosA->id);
        $this->assertEqualsWithDelta(-90000.0, $rowA['adjustments_net'], 0.005);
    }
}
