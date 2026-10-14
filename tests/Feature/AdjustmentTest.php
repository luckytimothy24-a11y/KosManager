<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\ManagementAgreement;
use App\Models\Payout;
use App\Models\PayoutItem;
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

class AdjustmentTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $superAdmin;

    private Kos $kos;

    private Payout $payout;

    private Pembayaran $pembayaranA;

    private Pembayaran $pembayaranB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $po = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $po->id]);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'property_owner_id' => $po->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);
        $penghuni = Penghuni::factory()->create(['user_id' => $tenant->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
        $kontrak = Kontrak::factory()->create(['penghuni_id' => $penghuni->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
        $tagihan = Tagihan::factory()->create(['penghuni_id' => $penghuni->id, 'kontrak_id' => $kontrak->id, 'kamar_id' => $kamar->id, 'subtotal' => 4000000, 'penalty' => 0, 'total' => 4000000]);
        ManagementAgreement::factory()->create(['kos_id' => $this->kos->id, 'property_owner_id' => $po->id, 'commission_type' => 'percent', 'commission_value' => 10, 'effective_from' => '2026-01-01']);
        $this->pembayaranA = Pembayaran::factory()->create(['tagihan_id' => $tagihan->id, 'penghuni_id' => $penghuni->id, 'amount' => 2000000, 'rent_amount' => 2000000, 'penalty_amount' => 0, 'payment_date' => '2026-09-10', 'verification_status' => 'approved']);
        $this->pembayaranB = Pembayaran::factory()->create(['tagihan_id' => $tagihan->id, 'penghuni_id' => $penghuni->id, 'amount' => 2000000, 'rent_amount' => 2000000, 'penalty_amount' => 0, 'payment_date' => '2026-09-12', 'verification_status' => 'approved']);
        $svc = app(PropertyOwnerPayoutService::class);
        $res = $svc->createForKos($this->owner, ['kos_id' => $this->kos->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30']);
        $this->payout = $res['payout'];
    }

    public function test_partial_refund_percent_calculates_delta(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $res = $svc->createAdjustment($this->superAdmin, ['pembayaran_id' => $this->pembayaranA->id, 'payout_id' => $this->payout->id, 'type' => 'refund', 'amount' => 500000, 'reason' => 'refund parsial tenant']);
        $this->assertTrue($res['ok']);
        $this->assertSame(50000.0, (float) $res['adjustment']->commission_delta);
        $this->assertSame(500000.0, (float) $res['adjustment']->amount);
    }

    public function test_full_refund_and_cumulative_exceeds_gross(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $r1 = $svc->createAdjustment($this->superAdmin, ['pembayaran_id' => $this->pembayaranA->id, 'payout_id' => $this->payout->id, 'type' => 'refund', 'amount' => 1000000, 'reason' => 'refund pertama']);
        $this->assertTrue($r1['ok']);
        $r2 = $svc->createAdjustment($this->superAdmin, ['pembayaran_id' => $this->pembayaranA->id, 'payout_id' => $this->payout->id, 'type' => 'refund', 'amount' => 1500000, 'reason' => 'refund kedua melebihi']);
        $this->assertFalse($r2['ok']);
        $this->assertSame('refund_exceeds_gross', $r2['error']);
    }

    public function test_fixed_commission_delta_is_zero(): void
    {
        $kos2 = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'property_owner_id' => $this->kos->property_owner_id]);
        // need separate flow: create fixed agreement on new kos
        $kamar2 = Kamar::factory()->create(['kos_id' => $kos2->id]);
        $tenant2 = User::factory()->create(['role' => 'tenant']);
        $penghuni2 = Penghuni::factory()->create(['user_id' => $tenant2->id, 'kos_id' => $kos2->id, 'kamar_id' => $kamar2->id, 'status' => 'active']);
        $kontrak2 = Kontrak::factory()->create(['penghuni_id' => $penghuni2->id, 'kos_id' => $kos2->id, 'kamar_id' => $kamar2->id, 'status' => 'active']);
        $tagihan2 = Tagihan::factory()->create(['penghuni_id' => $penghuni2->id, 'kontrak_id' => $kontrak2->id, 'kamar_id' => $kamar2->id, 'subtotal' => 2000000, 'penalty' => 0, 'total' => 2000000]);
        ManagementAgreement::factory()->create(['kos_id' => $kos2->id, 'property_owner_id' => $this->kos->property_owner_id, 'commission_type' => 'fixed', 'commission_value' => 150000, 'effective_from' => '2026-01-01']);
        $pay = Pembayaran::factory()->create(['tagihan_id' => $tagihan2->id, 'penghuni_id' => $penghuni2->id, 'amount' => 2000000, 'rent_amount' => 2000000, 'penalty_amount' => 0, 'payment_date' => '2026-09-15', 'verification_status' => 'approved']);
        $svc = app(PropertyOwnerPayoutService::class);
        $resPayout = $svc->createForKos($this->owner, ['kos_id' => $kos2->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30']);
        $this->assertTrue($resPayout['ok']);
        $adj = $svc->createAdjustment($this->superAdmin, ['pembayaran_id' => $pay->id, 'payout_id' => $resPayout['payout']->id, 'type' => 'refund', 'amount' => 500000, 'reason' => 'refund fixed test']);
        $this->assertTrue($adj['ok']);
        $this->assertSame(0.0, (float) $adj['adjustment']->commission_delta);
    }

    public function test_historis_immutable_payout_and_item(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $beforeAmount = (float) $this->payout->amount;
        $beforeNet = (float) PayoutItem::where('pembayaran_id', $this->pembayaranA->id)->value('net_amount');
        $svc->createAdjustment($this->superAdmin, ['pembayaran_id' => $this->pembayaranA->id, 'payout_id' => $this->payout->id, 'type' => 'correction', 'amount' => 200000, 'reason' => 'koreksi admin']);
        $this->assertSame($beforeAmount, (float) $this->payout->fresh()->amount);
        $this->assertSame($beforeNet, (float) PayoutItem::where('pembayaran_id', $this->pembayaranA->id)->value('net_amount'));
    }

    public function test_owner_cannot_create_adjustment_for_linked_payout(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $res = $svc->createAdjustment($this->owner, ['pembayaran_id' => $this->pembayaranA->id, 'payout_id' => $this->payout->id, 'type' => 'refund', 'amount' => 100000, 'reason' => 'owner coba koreksi']);
        $this->assertFalse($res['ok']);
        $this->assertSame('forbidden', $res['error']);
    }

    public function test_reason_required_and_invalid_amount(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $r1 = $svc->createAdjustment($this->superAdmin, ['pembayaran_id' => $this->pembayaranA->id, 'payout_id' => $this->payout->id, 'type' => 'refund', 'amount' => 0, 'reason' => 'valid reason']);
        $this->assertSame('invalid_amount', $r1['error']);
        $r2 = $svc->createAdjustment($this->superAdmin, ['pembayaran_id' => $this->pembayaranA->id, 'payout_id' => $this->payout->id, 'type' => 'refund', 'amount' => 100000, 'reason' => 'ab']);
        $this->assertSame('reason_required', $r2['error']);
    }

    public function test_carry_forward_adjustment_net(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $svc->createAdjustment($this->superAdmin, ['pembayaran_id' => $this->pembayaranA->id, 'payout_id' => $this->payout->id, 'type' => 'refund', 'amount' => 1000000, 'reason' => 'refund carry forward']);
        $report = app(SettlementReportService::class)->forOwner($this->owner);
        $this->assertSame(-900000.0, (float) $report['adjustments_net']);
        $this->assertSame(3600000.0, (float) $report['net_obligation']);
        $this->assertSame(2700000.0, (float) $report['remaining_raw']);
    }

    public function test_commission_based_on_rent_amount_when_penalty_filled(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $kos2 = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'property_owner_id' => $this->kos->property_owner_id]);
        $kamar2 = Kamar::factory()->create(['kos_id' => $kos2->id]);
        $tenant2 = User::factory()->create(['role' => 'tenant']);
        $penghuni2 = Penghuni::factory()->create(['user_id' => $tenant2->id, 'kos_id' => $kos2->id, 'kamar_id' => $kamar2->id, 'status' => 'active']);
        $kontrak2 = Kontrak::factory()->create(['penghuni_id' => $penghuni2->id, 'kos_id' => $kos2->id, 'kamar_id' => $kamar2->id, 'status' => 'active']);
        $tagihan2 = Tagihan::factory()->create(['penghuni_id' => $penghuni2->id, 'kontrak_id' => $kontrak2->id, 'kamar_id' => $kamar2->id, 'subtotal' => 5000000, 'penalty' => 500000, 'total' => 5500000]);
        ManagementAgreement::factory()->create(['kos_id' => $kos2->id, 'property_owner_id' => $this->kos->property_owner_id, 'commission_type' => 'percent', 'commission_value' => 10, 'effective_from' => '2026-01-01']);
        $pay = Pembayaran::factory()->create(['tagihan_id' => $tagihan2->id, 'penghuni_id' => $penghuni2->id, 'amount' => 5500000, 'rent_amount' => 5000000, 'penalty_amount' => 500000, 'payment_date' => '2026-09-20', 'verification_status' => 'approved']);
        $resPayout = $svc->createForKos($this->owner, ['kos_id' => $kos2->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30']);
        $this->assertTrue($resPayout['ok']);
        $adj = $svc->createAdjustment($this->superAdmin, ['pembayaran_id' => $pay->id, 'payout_id' => $resPayout['payout']->id, 'type' => 'refund', 'amount' => 1000000, 'reason' => 'commission basis test']);
        $this->assertTrue($adj['ok']);
        $this->assertSame(100000.0, (float) $adj['adjustment']->commission_delta);
    }

    public function test_refund_fallback_rent_amount_when_no_payout_item(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'property_owner_id' => $this->kos->property_owner_id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $penghuni = Penghuni::factory()->create(['user_id' => $tenant->id, 'kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
        $kontrak = Kontrak::factory()->create(['penghuni_id' => $penghuni->id, 'kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
        $tagihan = Tagihan::factory()->create(['penghuni_id' => $penghuni->id, 'kontrak_id' => $kontrak->id, 'kamar_id' => $kamar->id, 'subtotal' => 5000000, 'penalty' => 0, 'total' => 5000000]);
        ManagementAgreement::factory()->create(['kos_id' => $kos->id, 'property_owner_id' => $this->kos->property_owner_id, 'commission_type' => 'percent', 'commission_value' => 10, 'effective_from' => '2026-01-01']);
        $pay = Pembayaran::factory()->create(['tagihan_id' => $tagihan->id, 'penghuni_id' => $penghuni->id, 'amount' => 5000000, 'rent_amount' => 5000000, 'penalty_amount' => 0, 'payment_date' => '2026-09-20', 'verification_status' => 'approved']);
        // No PayoutItem created for this payment
        $adj = $svc->createAdjustment($this->superAdmin, ['pembayaran_id' => $pay->id, 'type' => 'refund', 'amount' => 1000000, 'reason' => 'refund tanpa payout item']);
        $this->assertTrue($adj['ok']);
        // Adjustment created successfully with amount 1000000, and gross rent check uses rent_amount (5000000) as basis
        // since no PayoutItem exists - the refund_exceeds_gross check passes because
        // existingRefunded(0) + amount(1000000) <= grossRent(5000000) + 0.005
        $this->assertSame(1000000.0, (float) $adj['adjustment']->amount);
    }
}
