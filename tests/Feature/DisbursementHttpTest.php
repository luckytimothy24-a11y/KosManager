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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DisbursementHttpTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $ownerOther;

    private User $superAdmin;

    private Payout $payout;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->ownerOther = User::factory()->create(['role' => 'owner']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $po = PropertyOwner::factory()->create();
        PropertyOwnerBankAccount::factory()->create(['property_owner_id' => $po->id]);
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'property_owner_id' => $po->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create(['user_id' => $tenant->id, 'kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
        $kontrak = Kontrak::factory()->create(['penghuni_id' => $penghuni->id, 'kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
        $tagihan = Tagihan::factory()->create(['penghuni_id' => $penghuni->id, 'kontrak_id' => $kontrak->id, 'kamar_id' => $kamar->id, 'subtotal' => 3000000, 'penalty' => 0, 'total' => 3000000]);
        ManagementAgreement::factory()->create(['kos_id' => $kos->id, 'property_owner_id' => $po->id, 'commission_type' => 'percent', 'commission_value' => 10, 'effective_from' => '2026-01-01']);
        Pembayaran::factory()->create(['tagihan_id' => $tagihan->id, 'penghuni_id' => $penghuni->id, 'amount' => 2000000, 'rent_amount' => 2000000, 'penalty_amount' => 0, 'payment_date' => '2026-09-10', 'verification_status' => 'approved']);
        Pembayaran::factory()->create(['tagihan_id' => $tagihan->id, 'penghuni_id' => $penghuni->id, 'amount' => 1000000, 'rent_amount' => 1000000, 'penalty_amount' => 0, 'payment_date' => '2026-09-12', 'verification_status' => 'approved']);
        $svc = app(PropertyOwnerPayoutService::class);
        $res = $svc->createForKos($this->owner, ['kos_id' => $kos->id, 'period_start' => '2026-09-01', 'period_end' => '2026-09-30']);
        $this->payout = $res['payout'];
    }

    public function test_owner_can_create_disbursement_via_http(): void
    {
        $this->actingAs($this->owner)->post(route('owner.payouts.disbursements.store', $this->payout), [
            'amount' => 1000000,
            'idempotency_key' => 'http-key-aaaaaaaa',
            'transferred_at' => '2026-09-20',
            'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ])->assertRedirect();
        $this->assertDatabaseHas('payout_disbursements', ['payout_id' => $this->payout->id, 'amount' => 1000000]);
    }

    public function test_same_key_same_payload_idempotent_http(): void
    {
        $payload = [
            'amount' => 500000,
            'idempotency_key' => 'http-key-bbbbbbbb',
            'transferred_at' => '2026-09-20',
            'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ];
        $this->actingAs($this->owner)->post(route('owner.payouts.disbursements.store', $this->payout), $payload)->assertRedirect();
        $this->actingAs($this->owner)->post(route('owner.payouts.disbursements.store', $this->payout), [
            'amount' => 500000,
            'idempotency_key' => 'http-key-bbbbbbbb',
            'transferred_at' => '2026-09-20',
            'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ])->assertRedirect();
        $this->assertSame(1, PayoutDisbursement::where('idempotency_key', 'http-key-bbbbbbbb')->count());
    }

    public function test_same_key_different_payload_conflict_http(): void
    {
        $this->actingAs($this->owner)->post(route('owner.payouts.disbursements.store', $this->payout), [
            'amount' => 1000000,
            'idempotency_key' => 'http-key-cccccccc',
            'transferred_at' => '2026-09-20',
            'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ])->assertRedirect();
        $this->actingAs($this->owner)->postJson(route('owner.payouts.disbursements.store', $this->payout), [
            'amount' => 500000,
            'idempotency_key' => 'http-key-cccccccc',
            'transferred_at' => '2026-09-20',
            'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ])->assertStatus(409)->assertJsonFragment(['error' => 'idempotency_conflict']);
    }

    public function test_overpayment_via_http_rejected(): void
    {
        $this->actingAs($this->owner)->post(route('owner.payouts.disbursements.store', $this->payout), [
            'amount' => 2000000,
            'idempotency_key' => 'http-key-dddddddd',
            'transferred_at' => '2026-09-20',
            'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ])->assertRedirect();
        $this->actingAs($this->owner)->postJson(route('owner.payouts.disbursements.store', $this->payout), [
            'amount' => 800000,
            'idempotency_key' => 'http-key-eeeeeeee',
            'transferred_at' => '2026-09-21',
            'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ])->assertStatus(422);
    }

    public function test_owner_cannot_verify_disbursement_via_http(): void
    {
        $this->actingAs($this->owner)->post(route('owner.payouts.disbursements.store', $this->payout), [
            'amount' => 500000,
            'idempotency_key' => 'http-key-ffffffff',
            'transferred_at' => '2026-09-20',
            'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ]);
        $d = PayoutDisbursement::where('idempotency_key', 'http-key-ffffffff')->firstOrFail();
        $this->actingAs($this->owner)->post(route('owner.disbursements.verify', $d))->assertForbidden();
    }

    public function test_super_admin_can_verify_via_http(): void
    {
        $this->actingAs($this->owner)->post(route('owner.payouts.disbursements.store', $this->payout), [
            'amount' => 500000,
            'idempotency_key' => 'http-key-gggggggg',
            'transferred_at' => '2026-09-20',
            'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ]);
        $d = PayoutDisbursement::where('idempotency_key', 'http-key-gggggggg')->firstOrFail();
        $this->actingAs($this->superAdmin)->post(route('owner.disbursements.verify', $d))->assertRedirect();
        $this->assertSame('verified', $d->fresh()->status);
    }

    public function test_idor_other_owner_forbidden_http(): void
    {
        $this->actingAs($this->ownerOther)->post(route('owner.payouts.disbursements.store', $this->payout), [
            'amount' => 100000,
            'idempotency_key' => 'http-key-hhhhhhhh',
            'transferred_at' => '2026-09-20',
            'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ])->assertForbidden();
    }

    public function test_without_idempotency_key_validation_error(): void
    {
        $this->actingAs($this->owner)->post(route('owner.payouts.disbursements.store', $this->payout), [
            'amount' => 100000,
            'transferred_at' => '2026-09-20',
            'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('idempotency_key');
    }
}
