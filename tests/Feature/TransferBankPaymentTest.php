<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TransferBankPaymentTest extends TestCase
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
        Storage::fake('local');

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'payment_info' => 'Transfer ke BCA 1234567890 a.n. Kos Manager']);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'available', 'monthly_price' => 800000]);
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
            'status' => 'unpaid',
            'total' => 800000,
            'subtotal' => 800000,
            'discount' => 0,
            'penalty' => 0,
        ]);
    }

    public function test_tenant_can_submit_transfer_bank_payment_with_proof(): void
    {
        $proof = UploadedFile::fake()->create('bukti-transfer.png', 200, 'image/png');

        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $this->tagihan->id,
            'amount' => 800000,
            'payment_method' => 'transfer_bank',
            'proof_file' => $proof,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pembayarans', [
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'payment_method' => 'transfer_bank',
            'verification_status' => 'pending',
        ]);
        $this->assertDatabaseHas('tagihans', [
            'id' => $this->tagihan->id,
            'status' => 'pending_verification',
        ]);
    }

    public function test_transfer_bank_payment_requires_proof(): void
    {
        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $this->tagihan->id,
            'amount' => 800000,
            'payment_method' => 'transfer_bank',
        ]);

        $response->assertSessionHasErrors('proof_file');
        $this->assertDatabaseCount('pembayarans', 0);
    }

    public function test_unknown_payment_method_is_rejected(): void
    {
        $proof = UploadedFile::fake()->create('bukti.png', 100, 'image/png');

        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $this->tagihan->id,
            'amount' => 800000,
            'payment_method' => 'e_wallet',
            'proof_file' => $proof,
        ]);

        $response->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('pembayarans', 0);
    }

    public function test_cash_payment_still_works_without_proof(): void
    {
        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $this->tagihan->id,
            'amount' => 800000,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pembayarans', [
            'tagihan_id' => $this->tagihan->id,
            'payment_method' => 'cash',
            'verification_status' => 'pending',
        ]);
    }
}
