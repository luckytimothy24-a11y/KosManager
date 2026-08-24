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
use Tests\TestCase;

class PaymentRejectionReasonTest extends TestCase
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

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'payment_info' => 'Transfer Bank BCA 1234567890 a.n. Pemilik',
        ]);
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
            'status' => 'pending_verification',
            'total' => 1500000,
        ]);
    }

    public function test_rejection_saves_reason_to_admin_notes(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 1500000,
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.pembayaran.reject', $pembayaran), [
            'reason' => 'Bukti transfer tidak sesuai nominal.',
        ]);

        $response->assertRedirect(route('owner.pembayaran.index'));

        $pembayaran->refresh();
        $this->assertEquals('rejected', $pembayaran->verification_status);
        $this->assertEquals('Bukti transfer tidak sesuai nominal.', $pembayaran->admin_notes);
        $this->assertNotNull($pembayaran->verified_at);
        $this->assertDatabaseHas('tagihans', ['id' => $this->tagihan->id, 'status' => 'unpaid']);
    }

    public function test_rejection_without_reason_still_works_backward_compatible(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 1500000,
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.pembayaran.reject', $pembayaran));

        $response->assertRedirect(route('owner.pembayaran.index'));
        $this->assertDatabaseHas('pembayarans', [
            'id' => $pembayaran->id,
            'verification_status' => 'rejected',
        ]);
    }

    public function test_tenant_sees_rejection_reason_and_repay_cta(): void
    {
        $this->tagihan->update(['status' => 'unpaid']);

        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 1500000,
            'verification_status' => 'rejected',
            'admin_notes' => 'Foto bukti tidak jelas, silakan unggah ulang.',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.pembayaran.show', $pembayaran));

        $response->assertStatus(200);
        $response->assertSee('Alasan Penolakan');
        $response->assertSee('Foto bukti tidak jelas, silakan unggah ulang.');
        $response->assertSee('Bayar Kembali');
    }

    public function test_owner_sees_pending_verification_shortcut_count(): void
    {
        Pembayaran::factory()->count(2)->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 1500000,
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($this->owner)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Verifikasi Pembayaran');
    }

    public function test_kos_payment_info_is_displayed_on_bill_page(): void
    {
        $this->tagihan->update(['status' => 'unpaid']);

        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertStatus(200);
        $response->assertSee('Informasi Pembayaran');
        $response->assertSee('Transfer Bank BCA 1234567890 a.n. Pemilik');
    }
}
