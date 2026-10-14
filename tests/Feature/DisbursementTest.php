<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\ManagementAgreement;
use App\Models\Payout;
use App\Models\PayoutDisbursement;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\PropertyOwner;
use App\Models\PropertyOwnerBankAccount;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\PropertyOwnerPayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisbursementTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $superAdmin;

    private User $ownerOther;

    private Kos $kos;

    private Payout $payout;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->ownerOther = User::factory()->create(['role' => 'owner']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $po = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $po->id]);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'property_owner_id' => $po->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);
        $penghuni = Penghuni::factory()->create(['user_id' => $tenant->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
        $kontrak = Kontrak::factory()->create(['penghuni_id' => $penghuni->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
        $tagihan = Tagihan::factory()->create(['penghuni_id' => $penghuni->id, 'kontrak_id' => $kontrak->id, 'kamar_id' => $kamar->id, 'subtotal' => 3000000, 'penalty' => 0, 'total' => 3000000]);
        ManagementAgreement::factory()->create(['kos_id' => $this->kos->id, 'property_owner_id' => $po->id, 'commission_type' => 'percent', 'commission_value' => 10, 'effective_from' => '2026-01-01']);
        Pembayaran::factory()->create(['tagihan_id' => $tagihan->id, 'penghuni_id' => $penghuni->id, 'amount' => 2000000, 'rent_amount' => 2000000, 'penalty_amount' => 0, 'payment_date' => '2026-09-10', 'verification_status' => 'approved']);
        Pembayaran::factory()->create(['tagihan_id' => $tagihan->id, 'penghuni_id' => $penghuni->id, 'amount' => 1000000, 'rent_amount' => 1000000, 'penalty_amount' => 0, 'payment_date' => '2026-09-12', 'verification_status' => 'approved']);
        $svc = app(PropertyOwnerPayoutService::class);
        $res = $svc->createForKos($this->owner, ['kos_id' => $this->kos->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30']);
        $this->assertTrue($res['ok'], json_encode($res));
        $this->payout = $res['payout'];
        $this->assertSame(2700000.0, (float) $this->payout->amount);
    }

    public function test_retry_same_key_same_payload_is_idempotent(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $data = ['amount' => 1000000, 'idempotency_key' => 'key-idempotent-11111111', 'proof_path' => 'payout-proof/a.pdf', 'transferred_at' => '2026-09-20'];
        $r1 = $svc->recordDisbursement($this->owner, $this->payout, $data);
        $this->assertTrue($r1['ok']);
        $r2 = $svc->recordDisbursement($this->owner, $this->payout, $data);
        $this->assertTrue($r2['ok']);
        $this->assertTrue($r2['duplicate'] ?? false);
        $this->assertSame($r1['disbursement']->id, $r2['disbursement']->id);
        $this->assertSame(1, PayoutDisbursement::where('idempotency_key', 'key-idempotent-11111111')->count());
    }

    public function test_same_key_different_payload_returns_conflict(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $r1 = $svc->recordDisbursement($this->owner, $this->payout, ['amount' => 1000000, 'idempotency_key' => 'key-conflict-22222222', 'proof_path' => 'payout-proof/a.pdf', 'transferred_at' => '2026-09-20']);
        $this->assertTrue($r1['ok']);
        $r2 = $svc->recordDisbursement($this->owner, $this->payout, ['amount' => 500000, 'idempotency_key' => 'key-conflict-22222222', 'proof_path' => 'payout-proof/b.pdf', 'transferred_at' => '2026-09-20']);
        $this->assertFalse($r2['ok']);
        $this->assertSame('idempotency_conflict', $r2['error']);
        $this->assertSame(1, PayoutDisbursement::where('idempotency_key', 'key-conflict-22222222')->count());
    }

    public function test_overpayment_rejected(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $r1 = $svc->recordDisbursement($this->owner, $this->payout, ['amount' => 2000000, 'idempotency_key' => 'key-overpay-aa-33333333', 'proof_path' => 'payout-proof/a.pdf', 'transferred_at' => '2026-09-20']);
        $this->assertTrue($r1['ok']);
        $r2 = $svc->recordDisbursement($this->owner, $this->payout, ['amount' => 800000, 'idempotency_key' => 'key-overpay-bb-44444444', 'proof_path' => 'payout-proof/b.pdf', 'transferred_at' => '2026-09-21']);
        $this->assertFalse($r2['ok']);
        $this->assertSame('overpayment', $r2['error']);
    }

    public function test_rejected_not_counted_and_overpayment_still_enforced(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $r1 = $svc->recordDisbursement($this->owner, $this->payout, ['amount' => 1000000, 'idempotency_key' => 'key-reject-55555555', 'proof_path' => 'payout-proof/a.pdf', 'transferred_at' => '2026-09-20']);
        $this->assertTrue($r1['ok']);
        $rej = $svc->rejectDisbursement($this->superAdmin, $r1['disbursement'], 'salah bukti');
        $this->assertTrue($rej['ok']);
        $r2 = $svc->recordDisbursement($this->owner, $this->payout, ['amount' => 2700000, 'idempotency_key' => 'key-reject-66666666', 'proof_path' => 'payout-proof/b.pdf', 'transferred_at' => '2026-09-21']);
        $this->assertTrue($r2['ok'], json_encode($r2));
        $r3 = $svc->recordDisbursement($this->owner, $this->payout, ['amount' => 10000, 'idempotency_key' => 'key-reject-77777777', 'proof_path' => 'payout-proof/c.pdf', 'transferred_at' => '2026-09-22']);
        $this->assertFalse($r3['ok']);
        $this->assertSame('overpayment', $r3['error']);
    }

    public function test_partial_transitions_to_verified_when_fully_verified(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $r1 = $svc->recordDisbursement($this->owner, $this->payout, ['amount' => 1700000, 'idempotency_key' => 'key-partial-88888888', 'proof_path' => 'payout-proof/a.pdf', 'transferred_at' => '2026-09-20']);
        $r2 = $svc->recordDisbursement($this->owner, $this->payout, ['amount' => 1000000, 'idempotency_key' => 'key-partial-99999999', 'proof_path' => 'payout-proof/b.pdf', 'transferred_at' => '2026-09-21']);
        $this->assertTrue($r1['ok']);
        $this->assertTrue($r2['ok']);
        $this->assertSame('pending', $this->payout->fresh()->status);
        $svc->verifyDisbursement($this->superAdmin, $r1['disbursement']);
        $this->assertNotSame(Payout::STATUS_VERIFIED, $this->payout->fresh()->status);
        $svc->verifyDisbursement($this->superAdmin, $r2['disbursement']->fresh());
        $this->assertSame(Payout::STATUS_VERIFIED, $this->payout->fresh()->status);
    }

    public function test_owner_cannot_verify_disbursement(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $r1 = $svc->recordDisbursement($this->owner, $this->payout, ['amount' => 500000, 'idempotency_key' => 'key-auth-10101010', 'proof_path' => 'payout-proof/a.pdf', 'transferred_at' => '2026-09-20']);
        $res = $svc->verifyDisbursement($this->owner, $r1['disbursement']);
        $this->assertFalse($res['ok']);
        $this->assertSame('forbidden', $res['error']);
        $res2 = $svc->verifyDisbursement($this->superAdmin, $r1['disbursement']);
        $this->assertTrue($res2['ok']);
    }

    public function test_idor_other_owner_forbidden(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $res = $svc->recordDisbursement($this->ownerOther, $this->payout, ['amount' => 100000, 'idempotency_key' => 'key-idor-12121212', 'proof_path' => 'payout-proof/a.pdf', 'transferred_at' => '2026-09-20']);
        $this->assertFalse($res['ok']);
        $this->assertSame('forbidden', $res['error']);
    }
}
