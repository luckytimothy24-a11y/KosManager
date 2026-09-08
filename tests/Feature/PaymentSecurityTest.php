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

class PaymentSecurityTest extends TestCase
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

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'tagihan_id' => $this->tagihan->id,
            'amount' => 1500000,
            'payment_method' => 'cash',
            'proof_file' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ], $overrides);
    }

    public function test_second_submission_while_pending_verification_is_blocked(): void
    {
        $first = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), $this->validPayload());
        $first->assertRedirect();

        $second = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), $this->validPayload());
        $second->assertRedirect();
        $second->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('pembayarans', 1);
    }

    public function test_proof_is_stored_on_private_disk_not_public(): void
    {
        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), $this->validPayload());
        $response->assertRedirect();

        $pembayaran = Pembayaran::firstOrFail();
        $this->assertNotNull($pembayaran->proof_file);
        $this->assertTrue(Storage::disk('local')->exists($pembayaran->proof_file));
        $this->assertFalse(Storage::disk('public')->exists($pembayaran->proof_file));
    }

    public function test_owner_can_download_proof_with_authorization(): void
    {
        Storage::put('bukti-pembayaran/test-proof.pdf', 'proof-content');

        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 1500000,
            'verification_status' => 'pending',
            'proof_file' => 'bukti-pembayaran/test-proof.pdf',
        ]);

        $guest = $this->get(route('pembayaran.proof', $pembayaran));
        $guest->assertRedirect(route('login'));

        $strangerTenant = User::factory()->create(['role' => 'tenant']);
        $forbidden = $this->actingAs($strangerTenant)->get(route('pembayaran.proof', $pembayaran));
        $forbidden->assertForbidden();

        $allowed = $this->actingAs($this->owner)->get(route('pembayaran.proof', $pembayaran));
        $allowed->assertOk();
    }

    public function test_verify_rejects_amount_mismatching_tagihan_total(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 100000,
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.pembayaran.verify', $pembayaran));

        $response->assertStatus(400);
        $this->assertDatabaseHas('pembayarans', [
            'id' => $pembayaran->id,
            'verification_status' => 'pending',
        ]);
        $this->assertDatabaseHas('tagihans', [
            'id' => $this->tagihan->id,
            'status' => 'unpaid',
        ]);
    }

    public function test_owner_a_cannot_download_owner_b_payment_proof(): void
    {
        Storage::put('bukti-pembayaran/owner-b-proof.pdf', 'proof-content');

        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'verification_status' => 'pending',
            'proof_file' => 'bukti-pembayaran/owner-b-proof.pdf',
        ]);

        $ownerB = User::factory()->create(['role' => 'owner']);

        $this->actingAs($ownerB)->get(route('pembayaran.proof', $pembayaran))->assertForbidden();
        $this->actingAs($ownerB)->get(route('owner.pembayaran.show', $pembayaran))->assertForbidden();

        $this->actingAs($this->owner)->get(route('pembayaran.proof', $pembayaran))->assertOk();
    }

    public function test_tenant_can_download_own_payment_proof(): void
    {
        Storage::put('bukti-pembayaran/own-proof.pdf', 'proof-content');

        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'verification_status' => 'pending',
            'proof_file' => 'bukti-pembayaran/own-proof.pdf',
        ]);

        $this->actingAs($this->tenant)->get(route('pembayaran.proof', $pembayaran))->assertOk();
    }

    public function test_missing_proof_file_returns_404(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'verification_status' => 'pending',
            'proof_file' => null,
        ]);

        $this->actingAs($this->owner)->get(route('pembayaran.proof', $pembayaran))->assertNotFound();
    }
}
