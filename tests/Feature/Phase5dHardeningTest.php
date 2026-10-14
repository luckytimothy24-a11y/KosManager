<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\ManagementAgreement;
use App\Models\Payout;
use App\Models\PayoutAdjustment;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\PropertyOwner;
use App\Models\PropertyOwnerBankAccount;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\PropertyOwnerPayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase5dHardeningTest extends TestCase
{
    use RefreshDatabase;

    private User $ownerA;

    private User $ownerB;

    private User $superAdmin;

    private Kos $kosA;

    private Kos $kosB;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        $this->ownerA = User::factory()->create(['role' => 'owner']);
        $this->ownerB = User::factory()->create(['role' => 'owner']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $tenantA = User::factory()->create(['role' => 'tenant']);
        $tenantB = User::factory()->create(['role' => 'tenant']);
        $poA = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $poA->id]);
        $poB = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $poB->id]);
        $this->kosA = Kos::factory()->create(['owner_id' => $this->ownerA->id, 'status' => 'active', 'property_owner_id' => $poA->id]);
        $this->kosB = Kos::factory()->create(['owner_id' => $this->ownerB->id, 'status' => 'active', 'property_owner_id' => $poB->id]);
        foreach ([[$this->kosA, $poA, $tenantA], [$this->kosB, $poB, $tenantB]] as [$kos, $po, $tenant]) {
            ManagementAgreement::factory()->create(['kos_id' => $kos->id, 'property_owner_id' => $po->id, 'commission_type' => 'percent', 'commission_value' => 10, 'effective_from' => '2026-01-01']);
            $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
            $penghuni = Penghuni::factory()->create(['user_id' => $tenant->id, 'kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
            $kontrak = Kontrak::factory()->create(['penghuni_id' => $penghuni->id, 'kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
            $tagihan = Tagihan::factory()->create(['penghuni_id' => $penghuni->id, 'kontrak_id' => $kontrak->id, 'kamar_id' => $kamar->id, 'subtotal' => 2000000, 'penalty' => 0, 'total' => 2000000]);
            Pembayaran::factory()->create(['tagihan_id' => $tagihan->id, 'penghuni_id' => $penghuni->id, 'amount' => 2000000, 'rent_amount' => 2000000, 'penalty_amount' => 0, 'payment_date' => '2026-09-10', 'verification_status' => 'approved']);
        }
        $svc = app(PropertyOwnerPayoutService::class);
        $svc->createForKos($this->ownerA, ['kos_id' => $this->kosA->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30']);
        $svc->createForKos($this->ownerB, ['kos_id' => $this->kosB->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30']);
    }

    public function test_owner_orphan_other_kos_forbidden(): void
    {
        $pembayaranB = Pembayaran::whereHas('penghuni.kos', fn ($q) => $q->whereKey($this->kosB->id))->firstOrFail();
        $this->actingAs($this->ownerA)->post(route('owner.adjustments.store'), [
            'pembayaran_id' => $pembayaranB->id,
            'type' => 'refund',
            'amount' => 100000,
            'reason' => 'orphan lintas kos harus ditolak',
        ])->assertForbidden();
        $this->assertSame(0, PayoutAdjustment::where('pembayaran_id', $pembayaranB->id)->count());
    }

    public function test_owner_orphan_own_kos_allowed(): void
    {
        $penghuni = Penghuni::where('kos_id', $this->kosA->id)->firstOrFail();
        $tagihan = Tagihan::where('penghuni_id', $penghuni->id)->firstOrFail();
        $pay = Pembayaran::factory()->create(['tagihan_id' => $tagihan->id, 'penghuni_id' => $penghuni->id, 'amount' => 500000, 'rent_amount' => 500000, 'penalty_amount' => 0, 'payment_date' => '2026-10-05', 'verification_status' => 'approved']);
        $this->actingAs($this->ownerA)->post(route('owner.adjustments.store'), [
            'pembayaran_id' => $pay->id,
            'type' => 'refund',
            'amount' => 100000,
            'reason' => 'orphan kos sendiri ditolak D1 SuperAdmin only',
        ])->assertForbidden();
        $this->assertDatabaseMissing('payout_adjustments', ['pembayaran_id' => $pay->id]);
    }

    public function test_owner_commission_delta_ignored(): void
    {
        $penghuni = Penghuni::where('kos_id', $this->kosA->id)->firstOrFail();
        $tagihan = Tagihan::where('penghuni_id', $penghuni->id)->firstOrFail();
        $pay = Pembayaran::factory()->create(['tagihan_id' => $tagihan->id, 'penghuni_id' => $penghuni->id, 'amount' => 1000000, 'rent_amount' => 1000000, 'penalty_amount' => 0, 'payment_date' => '2026-10-06', 'verification_status' => 'approved']);
        $this->actingAs($this->ownerA)->post(route('owner.adjustments.store'), [
            'pembayaran_id' => $pay->id,
            'type' => 'refund',
            'amount' => 500000,
            'reason' => 'commission delta should be ignored for owner',
            'commission_delta' => 99999,
        ])->assertForbidden();
        $this->assertSame(0, PayoutAdjustment::where('pembayaran_id', $pay->id)->count());
    }

    public function test_super_admin_commission_delta_allowed(): void
    {
        $pembayaranA = Pembayaran::whereHas('penghuni.kos', fn ($q) => $q->whereKey($this->kosA->id))->firstOrFail();
        $penghuni = Penghuni::where('kos_id', $this->kosA->id)->firstOrFail();
        $tagihan = Tagihan::where('penghuni_id', $penghuni->id)->firstOrFail();
        $pay = Pembayaran::factory()->create(['tagihan_id' => $tagihan->id, 'penghuni_id' => $penghuni->id, 'amount' => 1000000, 'rent_amount' => 1000000, 'penalty_amount' => 0, 'payment_date' => '2026-10-07', 'verification_status' => 'approved']);
        $this->actingAs($this->superAdmin)->post(route('owner.adjustments.store'), [
            'pembayaran_id' => $pay->id,
            'type' => 'refund',
            'amount' => 500000,
            'reason' => 'super admin boleh override delta',
            'commission_delta' => 12345,
        ])->assertSessionHasNoErrors();
        $adj = PayoutAdjustment::where('pembayaran_id', $pay->id)->firstOrFail();
        $this->assertSame(12345.0, (float) $adj->commission_delta);
    }

    public function test_same_key_different_date_conflict(): void
    {
        $payout = Payout::where('kos_id', $this->kosA->id)->firstOrFail();
        $svc = app(PropertyOwnerPayoutService::class);
        $r1 = $svc->recordDisbursement($this->ownerA, $payout, ['amount' => 500000, 'idempotency_key' => 'hardening-date-11111111', 'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'), 'transferred_at' => '2026-09-20']);
        $this->assertTrue($r1['ok']);
        $r2 = $svc->recordDisbursement($this->ownerA, $payout, ['amount' => 500000, 'idempotency_key' => 'hardening-date-11111111', 'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'), 'transferred_at' => '2026-09-21']);
        $this->assertFalse($r2['ok']);
        $this->assertSame('idempotency_conflict', $r2['error']);
    }

    public function test_reject_disbursement_requires_reason_via_request(): void
    {
        $payout = Payout::where('kos_id', $this->kosA->id)->firstOrFail();
        $svc = app(PropertyOwnerPayoutService::class);
        $r = $svc->recordDisbursement($this->ownerA, $payout, ['amount' => 500000, 'idempotency_key' => 'hardening-reject-22222222', 'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'), 'transferred_at' => '2026-09-20']);
        $d = $r['disbursement'];
        $this->actingAs($this->superAdmin)->post(route('owner.disbursements.reject', $d), ['reason' => 'ab'])->assertSessionHasErrors('reason');
        $this->actingAs($this->superAdmin)->post(route('owner.disbursements.reject', $d), ['reason' => 'alasan valid penolakan'])->assertRedirect();
        $this->assertSame('rejected', $d->fresh()->status);
    }

    public function test_mark_transferred_with_locking(): void
    {
        $payout = Payout::where('kos_id', $this->kosA->id)->firstOrFail();
        $this->assertSame(Payout::STATUS_PENDING, $payout->status);
        $res = $this->actingAs($this->ownerA)->post(route('owner.payouts.transfer', $payout), [
            'transferred_at' => '2026-09-20',
            'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ]);
        $res->assertRedirect();
        $this->assertSame(Payout::STATUS_TRANSFERRED, $payout->fresh()->status);
    }
}
