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

class IdempotencyNotesTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Payout $payout;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
        $this->owner = User::factory()->create(['role' => 'owner']);
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

    public function test_same_key_different_notes_conflict(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $r1 = $svc->recordDisbursement($this->owner, $this->payout, ['amount' => 500000, 'idempotency_key' => 'notes-key-aaaaaaaa', 'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'), 'transferred_at' => '2026-09-20', 'notes' => 'Transfer 1']);
        $this->assertTrue($r1['ok']);
        $r2 = $svc->recordDisbursement($this->owner, $this->payout, ['amount' => 500000, 'idempotency_key' => 'notes-key-aaaaaaaa', 'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'), 'transferred_at' => '2026-09-20', 'notes' => 'Transfer 2 different']);
        $this->assertFalse($r2['ok']);
        $this->assertSame('idempotency_conflict', $r2['error']);
        $this->assertSame(1, PayoutDisbursement::where('idempotency_key', 'notes-key-aaaaaaaa')->count());
    }

    public function test_same_key_same_notes_normalized_is_idempotent(): void
    {
        $svc = app(PropertyOwnerPayoutService::class);
        $r1 = $svc->recordDisbursement($this->owner, $this->payout, ['amount' => 500000, 'idempotency_key' => 'notes-key-bbbbbbbb', 'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'), 'transferred_at' => '2026-09-20', 'notes' => '  Transfer   1  ']);
        $this->assertTrue($r1['ok']);
        $r2 = $svc->recordDisbursement($this->owner, $this->payout, ['amount' => 500000, 'idempotency_key' => 'notes-key-bbbbbbbb', 'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'), 'transferred_at' => '2026-09-20', 'notes' => 'transfer 1']);
        $this->assertTrue($r2['ok']);
        $this->assertTrue($r2['duplicate'] ?? false);
        $this->assertSame(1, PayoutDisbursement::where('idempotency_key', 'notes-key-bbbbbbbb')->count());
    }
}
