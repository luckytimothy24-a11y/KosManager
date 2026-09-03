<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PembayaranTest extends TestCase
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
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
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
            'status' => 'unpaid',
            'total' => 1500000,
        ]);
    }

    public function test_tenant_can_upload_payment_proof(): void
    {
        $proof = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $this->tagihan->id,
            'amount' => 1500000,
            'payment_method' => 'transfer_bank',
            'proof_file' => $proof,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pembayarans', [
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 1500000,
            'verification_status' => 'pending',
        ]);
        $this->assertDatabaseHas('tagihans', [
            'id' => $this->tagihan->id,
            'status' => 'pending_verification',
        ]);
    }

    public function test_owner_can_verify_payment(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 1500000,
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.pembayaran.verify', $pembayaran));
        $response->assertRedirect(route('owner.pembayaran.index'));

        $this->assertDatabaseHas('pembayarans', [
            'id' => $pembayaran->id,
            'verification_status' => 'approved',
        ]);
        $this->assertDatabaseHas('tagihans', [
            'id' => $this->tagihan->id,
            'status' => 'paid',
        ]);
    }

    public function test_owner_can_reject_payment(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 1500000,
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.pembayaran.reject', $pembayaran), [
            'reason' => 'Bukti tidak jelas, mohon unggah ulang.',
        ]);
        $this->assertDatabaseHas('pembayarans', [
            'id' => $pembayaran->id,
            'verification_status' => 'rejected',
        ]);
    }

    public function test_tenant_cannot_verify_payment(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 1500000,
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($this->tenant)->post(route('owner.pembayaran.verify', $pembayaran));
        $response->assertStatus(403);
    }

    public function test_owner_can_view_payment_list(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.pembayaran.index'));
        $response->assertStatus(200);
    }

    public function test_tenant_can_view_payment_list(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.pembayaran.index'));
        $response->assertStatus(200);
    }

    public function test_owner_can_view_payment_detail(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
        ]);

        $response = $this->actingAs($this->owner)->get(route('owner.pembayaran.show', $pembayaran));
        $response->assertStatus(200);
    }

    public function test_payment_amount_must_match_tagihan_total(): void
    {
        $proof = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $this->tagihan->id,
            'amount' => 100000,
            'payment_method' => 'transfer_bank',
            'proof_file' => $proof,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('pembayarans', 0);
        $this->assertDatabaseHas('tagihans', ['id' => $this->tagihan->id, 'status' => 'unpaid']);
    }
}
