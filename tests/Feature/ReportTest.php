<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\ManagementAgreement;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\PropertyOwner;
use App\Models\PropertyOwnerBankAccount;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\PropertyOwnerPayoutService;
use App\Services\SettlementReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Kos $kosA;

    private Kos $kosB;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        $this->owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $po = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $po->id]);
        $poB = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $poB->id]);
        $this->kosA = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'property_owner_id' => $po->id]);
        $this->kosB = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'property_owner_id' => $poB->id]);
        foreach ([$this->kosA, $this->kosB] as $kos) {
            $poId = $kos->property_owner_id;
            ManagementAgreement::factory()->create(['kos_id' => $kos->id, 'property_owner_id' => $poId, 'commission_type' => 'percent', 'commission_value' => 10, 'effective_from' => '2026-01-01']);
            $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
            $penghuni = Penghuni::factory()->create(['user_id' => $tenant->id, 'kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
            $kontrak = Kontrak::factory()->create(['penghuni_id' => $penghuni->id, 'kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
            $tagihan = Tagihan::factory()->create(['penghuni_id' => $penghuni->id, 'kontrak_id' => $kontrak->id, 'kamar_id' => $kamar->id, 'subtotal' => 2000000, 'penalty' => 0, 'total' => 2000000]);
            Pembayaran::factory()->create(['tagihan_id' => $tagihan->id, 'penghuni_id' => $penghuni->id, 'amount' => 2000000, 'rent_amount' => 2000000, 'penalty_amount' => 0, 'payment_date' => '2026-09-10', 'verification_status' => 'approved']);
        }
    }

    public function test_global_received_equals_sum_per_kos(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $svc->createForKos($this->owner, ['kos_id' => $this->kosA->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30']);
        $svc->createForKos($this->owner, ['kos_id' => $this->kosB->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30']);
        $report = app(SettlementReportService::class)->forOwner($this->owner);
        $sumPerKos = array_sum(array_column($report['per_kos'], 'received'));
        $this->assertSame((float) $report['received'], $sumPerKos);
        $this->assertSame(4000000.0, (float) $report['received']);
    }

    public function test_after_full_payout_remaining_is_net_minus_verified(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $resA = $svc->createForKos($this->owner, ['kos_id' => $this->kosA->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30']);
        $payoutA = $resA['payout'];
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $d1 = $svc->recordDisbursement($this->owner, $payoutA, ['amount' => (float) $payoutA->amount, 'idempotency_key' => 'rpt-full-aaaaaaaa', 'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'), 'transferred_at' => '2026-09-20']);
        $this->assertTrue($d1['ok']);
        $svc->verifyDisbursement($superAdmin, $d1['disbursement']);
        $report = app(SettlementReportService::class)->forOwner($this->owner);
        // only kosA payout created, kosB not yet created -> net 1.8M verified 1.8M remaining 0
        $this->assertSame(1800000.0, (float) $report['payout_verified']);
        $this->assertSame(0.0, (float) $report['remaining_raw']);
    }

    public function test_pending_not_subtracted_from_remaining(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $resA = $svc->createForKos($this->owner, ['kos_id' => $this->kosA->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30']);
        $payoutA = $resA['payout'];
        $svc->recordDisbursement($this->owner, $payoutA, ['amount' => 1000000, 'idempotency_key' => 'rpt-pend-bbbbbbbb', 'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'), 'transferred_at' => '2026-09-20']);
        $report = app(SettlementReportService::class)->forOwner($this->owner);
        // pending should be 1M but remaining still net 1.8M (pending not subtract; only kosA payout exists)
        $this->assertSame(1000000.0, (float) $report['payout_pending']);
        $this->assertSame(1800000.0, (float) $report['remaining_raw']);
    }

    public function test_refund_reduces_remaining_and_creates_credit_when_over(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $resA = $svc->createForKos($this->owner, ['kos_id' => $this->kosA->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30']);
        $payoutA = $resA['payout'];
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $d1 = $svc->recordDisbursement($this->owner, $payoutA, ['amount' => 1800000, 'idempotency_key' => 'rpt-refund-cccccccc', 'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'), 'transferred_at' => '2026-09-20']);
        $svc->verifyDisbursement($superAdmin, $d1['disbursement']);
        $pembayaran = Pembayaran::whereHas('penghuni.kos', fn ($q) => $q->whereKey($this->kosA->id))->first();
        $svc->createAdjustment($superAdmin, ['pembayaran_id' => $pembayaran->id, 'payout_id' => $payoutA->id, 'type' => 'refund', 'amount' => 2000000, 'reason' => 'refund full untuk laporan']);
        $report = app(SettlementReportService::class)->forOwner($this->owner);
        // only kosA payout: net 1.8M, adj -1.8M, verified 1.8M => remaining -1.8M => credit 1.8M
        $this->assertSame(0.0, (float) $report['remaining_display']);
        $this->assertSame(1800000.0, (float) $report['credit_balance']);
        $this->assertSame(-1800000.0, (float) $report['adjustments_net']);
    }

    public function test_commission_not_double_counted(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $svc->createForKos($this->owner, ['kos_id' => $this->kosA->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30']);
        $svc->createForKos($this->owner, ['kos_id' => $this->kosB->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30']);
        $report = app(SettlementReportService::class)->forOwner($this->owner);
        $this->assertSame(400000.0, (float) $report['commission']);
        $this->assertSame(3600000.0, (float) $report['net_obligation']);
    }
}
