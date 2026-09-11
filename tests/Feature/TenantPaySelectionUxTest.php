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

class TenantPaySelectionUxTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private User $otherTenant;

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
        $this->otherTenant = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'payment_info' => 'Transfer ke BCA 1234567890 a.n. Kos Manager']);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'occupied', 'monthly_price' => 800000]);
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

    public function test_tenant_can_open_pay_page_and_see_eligible_bill(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.pay'));

        $response->assertOk();
        $response->assertSee('Pilih Tagihan');
        $response->assertSee('Ada 1 tagihan yang bisa dibayar.');
        $response->assertSee(e($this->tagihan->bill_number), false);
        $response->assertSee('Otomatis memilih satu-satunya tagihan yang tersedia.');
        $response->assertSee('Pesan (opsional)');
    }

    public function test_pay_page_lists_only_own_payable_bills(): void
    {
        $paid = Tagihan::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kontrak_id' => $this->kontrak->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'paid',
        ]);
        $pending = Tagihan::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kontrak_id' => $this->kontrak->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'pending_verification',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.pay'));

        $response->assertOk();
        $response->assertSee('Ada 1 tagihan yang bisa dibayar.');
        $response->assertSee(e($this->tagihan->bill_number), false);
        $response->assertDontSee(e($paid->bill_number), false);
        $response->assertDontSee(e($pending->bill_number), false);
    }

    public function test_pay_page_lists_all_payable_bills_when_multiple(): void
    {
        $second = Tagihan::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kontrak_id' => $this->kontrak->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'overdue',
            'total' => 500000,
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.pay'));

        $response->assertOk();
        $response->assertSee('Ada 2 tagihan yang bisa dibayar.');
        $response->assertSee(e($this->tagihan->bill_number), false);
        $response->assertSee(e($second->bill_number), false);
        $response->assertDontSee('Otomatis memilih satu-satunya tagihan yang tersedia.');
    }

    public function test_pay_page_empty_state_when_nothing_to_pay(): void
    {
        $this->tagihan->update(['status' => 'paid']);

        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.pay'));

        $response->assertOk();
        $response->assertSee('Belum ada tagihan yang perlu dibayar');
        $response->assertSee('Semua tagihan Anda sudah lunas');
    }

    public function test_payment_uses_the_selected_tagihan_and_stores_message(): void
    {
        $proof = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $this->tagihan->id,
            'amount' => 800000,
            'payment_method' => 'transfer_bank',
            'proof_file' => $proof,
            'notes' => 'Terima kasih sudah dibantu.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pembayarans', [
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 800000,
            'payment_method' => 'transfer_bank',
            'verification_status' => 'pending',
            'notes' => 'Terima kasih sudah dibantu.',
        ]);
        $this->assertDatabaseHas('tagihans', [
            'id' => $this->tagihan->id,
            'status' => 'pending_verification',
        ]);
    }

    public function test_empty_message_is_valid_and_stored_as_null(): void
    {
        $proof = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $this->tagihan->id,
            'amount' => 800000,
            'payment_method' => 'cash',
            'proof_file' => $proof,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pembayarans', [
            'tagihan_id' => $this->tagihan->id,
            'verification_status' => 'pending',
            'notes' => null,
        ]);
    }

    public function test_tenant_cannot_pay_other_tenant_bill(): void
    {
        $otherPenghuni = Penghuni::factory()->create([
            'user_id' => $this->otherTenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);
        $otherKontrak = Kontrak::factory()->create([
            'penghuni_id' => $otherPenghuni->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);
        $foreignBill = Tagihan::factory()->create([
            'penghuni_id' => $otherPenghuni->id,
            'kontrak_id' => $otherKontrak->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'unpaid',
            'total' => 600000,
        ]);

        $page = $this->actingAs($this->tenant)->get(route('tenant.tagihan.pay'));
        $page->assertOk();
        $page->assertSee('Ada 1 tagihan yang bisa dibayar.');
        $page->assertDontSee(e($foreignBill->bill_number), false);

        $proof = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');
        $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $foreignBill->id,
            'amount' => 600000,
            'payment_method' => 'cash',
            'proof_file' => $proof,
        ])->assertForbidden();

        $this->assertDatabaseMissing('pembayarans', ['tagihan_id' => $foreignBill->id]);
    }

    public function test_non_tenant_cannot_open_pay_page(): void
    {
        $this->actingAs($this->owner)
            ->get(route('tenant.tagihan.pay'))
            ->assertForbidden();
    }

    public function test_dashboard_hubungi_owner_cta_links_to_existing_chat(): void
    {
        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Butuh bantuan? Hubungi Owner');
        $response->assertSee(route('tenant.messages.index'), false);
    }

    public function test_tenant_with_multiple_penghunis_can_pay_second_penghuni_bill(): void
    {
        $kamar2 = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'occupied', 'monthly_price' => 700000]);
        $penghuni2 = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar2->id,
            'status' => 'active',
        ]);
        $kontrak2 = Kontrak::factory()->create([
            'penghuni_id' => $penghuni2->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar2->id,
            'status' => 'active',
        ]);
        $tagihan2 = Tagihan::factory()->create([
            'penghuni_id' => $penghuni2->id,
            'kontrak_id' => $kontrak2->id,
            'kamar_id' => $kamar2->id,
            'status' => 'unpaid',
            'total' => 700000,
            'subtotal' => 700000,
            'discount' => 0,
            'penalty' => 0,
        ]);

        $page = $this->actingAs($this->tenant)->get(route('tenant.tagihan.pay'));
        $page->assertOk();
        $page->assertSee('Ada 2 tagihan yang bisa dibayar.');

        $proof = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');
        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $tagihan2->id,
            'amount' => 700000,
            'payment_method' => 'transfer_bank',
            'proof_file' => $proof,
            'notes' => 'Bayar tagihan penghuni kedua.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pembayarans', [
            'tagihan_id' => $tagihan2->id,
            'penghuni_id' => $penghuni2->id,
            'amount' => 700000,
            'payment_method' => 'transfer_bank',
            'verification_status' => 'pending',
            'notes' => 'Bayar tagihan penghuni kedua.',
        ]);
        $this->assertDatabaseHas('tagihans', [
            'id' => $tagihan2->id,
            'status' => 'pending_verification',
        ]);
    }

    public function test_tenant_cannot_pay_other_tenant_bill_across_multiple_penghunis(): void
    {
        $kamar2 = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'occupied', 'monthly_price' => 700000]);
        $penghuni2 = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar2->id,
            'status' => 'active',
        ]);
        $kontrak2 = Kontrak::factory()->create([
            'penghuni_id' => $penghuni2->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar2->id,
            'status' => 'active',
        ]);

        $foreignPenghuni = Penghuni::factory()->create([
            'user_id' => $this->otherTenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar2->id,
            'status' => 'active',
        ]);
        $foreignKontrak = Kontrak::factory()->create([
            'penghuni_id' => $foreignPenghuni->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar2->id,
            'status' => 'active',
        ]);
        $foreignBill = Tagihan::factory()->create([
            'penghuni_id' => $foreignPenghuni->id,
            'kontrak_id' => $foreignKontrak->id,
            'kamar_id' => $kamar2->id,
            'status' => 'unpaid',
            'total' => 700000,
        ]);

        $proof = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');
        $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $foreignBill->id,
            'amount' => 700000,
            'payment_method' => 'cash',
            'proof_file' => $proof,
        ])->assertForbidden();

        $this->assertDatabaseMissing('pembayarans', ['tagihan_id' => $foreignBill->id]);
    }

    public function test_full_payment_flow_cash_with_message_on_second_penghuni(): void
    {
        $kamar2 = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'occupied', 'monthly_price' => 700000]);
        $penghuni2 = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar2->id,
            'status' => 'active',
        ]);
        $kontrak2 = Kontrak::factory()->create([
            'penghuni_id' => $penghuni2->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar2->id,
            'status' => 'active',
        ]);
        $tagihan2 = Tagihan::factory()->create([
            'penghuni_id' => $penghuni2->id,
            'kontrak_id' => $kontrak2->id,
            'kamar_id' => $kamar2->id,
            'status' => 'overdue',
            'total' => 700000,
            'subtotal' => 700000,
            'discount' => 0,
            'penalty' => 0,
        ]);

        $page = $this->actingAs($this->tenant)->get(route('tenant.tagihan.pay'));
        $page->assertOk();
        $page->assertSee('Ada 2 tagihan yang bisa dibayar.');

        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $tagihan2->id,
            'amount' => 700000,
            'payment_method' => 'cash',
            'notes' => 'Halo Owner, saya ingin menanyakan pembayaran tagihan ini.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('pembayarans', [
            'tagihan_id' => $tagihan2->id,
            'penghuni_id' => $penghuni2->id,
            'amount' => 700000,
            'payment_method' => 'cash',
            'verification_status' => 'pending',
            'notes' => 'Halo Owner, saya ingin menanyakan pembayaran tagihan ini.',
        ]);
        $this->assertDatabaseHas('tagihans', [
            'id' => $tagihan2->id,
            'status' => 'pending_verification',
        ]);

        $pembayaran = \App\Models\Pembayaran::where('tagihan_id', $tagihan2->id)->first();
        $this->assertEquals($penghuni2->id, $pembayaran->penghuni_id, 'penghuni_id must match tagihan owner, not first penghuni');
    }

    public function test_full_payment_flow_cash_without_message_on_second_penghuni(): void
    {
        $kamar2 = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'occupied', 'monthly_price' => 700000]);
        $penghuni2 = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar2->id,
            'status' => 'active',
        ]);
        $kontrak2 = Kontrak::factory()->create([
            'penghuni_id' => $penghuni2->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar2->id,
            'status' => 'active',
        ]);
        $tagihan2 = Tagihan::factory()->create([
            'penghuni_id' => $penghuni2->id,
            'kontrak_id' => $kontrak2->id,
            'kamar_id' => $kamar2->id,
            'status' => 'unpaid',
            'total' => 700000,
            'subtotal' => 700000,
            'discount' => 0,
            'penalty' => 0,
        ]);

        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $tagihan2->id,
            'amount' => 700000,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('pembayarans', [
            'tagihan_id' => $tagihan2->id,
            'penghuni_id' => $penghuni2->id,
            'amount' => 700000,
            'payment_method' => 'cash',
            'verification_status' => 'pending',
            'notes' => null,
        ]);
    }

    public function test_full_payment_flow_transfer_bank_with_proof_on_second_penghuni(): void
    {
        $kamar2 = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'occupied', 'monthly_price' => 700000]);
        $penghuni2 = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar2->id,
            'status' => 'active',
        ]);
        $kontrak2 = Kontrak::factory()->create([
            'penghuni_id' => $penghuni2->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar2->id,
            'status' => 'active',
        ]);
        $tagihan2 = Tagihan::factory()->create([
            'penghuni_id' => $penghuni2->id,
            'kontrak_id' => $kontrak2->id,
            'kamar_id' => $kamar2->id,
            'status' => 'unpaid',
            'total' => 700000,
            'subtotal' => 700000,
            'discount' => 0,
            'penalty' => 0,
        ]);

        $proof = UploadedFile::fake()->create('bukti-transfer.pdf', 100, 'application/pdf');
        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $tagihan2->id,
            'amount' => 700000,
            'payment_method' => 'transfer_bank',
            'proof_file' => $proof,
            'notes' => 'Sudah transfer, mohon dicek.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('pembayarans', [
            'tagihan_id' => $tagihan2->id,
            'penghuni_id' => $penghuni2->id,
            'amount' => 700000,
            'payment_method' => 'transfer_bank',
            'verification_status' => 'pending',
            'notes' => 'Sudah transfer, mohon dicek.',
        ]);

        $pembayaran = \App\Models\Pembayaran::where('tagihan_id', $tagihan2->id)->first();
        $this->assertNotNull($pembayaran->proof_file, 'proof_file must be stored for transfer_bank');
    }

    public function test_transfer_bank_without_proof_is_rejected(): void
    {
        $kamar2 = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'occupied', 'monthly_price' => 700000]);
        $penghuni2 = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar2->id,
            'status' => 'active',
        ]);
        $kontrak2 = Kontrak::factory()->create([
            'penghuni_id' => $penghuni2->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar2->id,
            'status' => 'active',
        ]);
        $tagihan2 = Tagihan::factory()->create([
            'penghuni_id' => $penghuni2->id,
            'kontrak_id' => $kontrak2->id,
            'kamar_id' => $kamar2->id,
            'status' => 'unpaid',
            'total' => 700000,
        ]);

        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $tagihan2->id,
            'amount' => 700000,
            'payment_method' => 'transfer_bank',
        ]);

        $response->assertSessionHasErrors('proof_file');
        $this->assertDatabaseMissing('pembayarans', ['tagihan_id' => $tagihan2->id]);
    }

    public function test_bill_switching_updates_amount_and_state(): void
    {
        $kamar2 = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'occupied', 'monthly_price' => 600000]);
        $penghuni2 = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar2->id,
            'status' => 'active',
        ]);
        $kontrak2 = Kontrak::factory()->create([
            'penghuni_id' => $penghuni2->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar2->id,
            'status' => 'active',
        ]);
        $tagihan2 = Tagihan::factory()->create([
            'penghuni_id' => $penghuni2->id,
            'kontrak_id' => $kontrak2->id,
            'kamar_id' => $kamar2->id,
            'status' => 'overdue',
            'total' => 600000,
            'subtotal' => 600000,
            'discount' => 0,
            'penalty' => 0,
        ]);

        $page = $this->actingAs($this->tenant)->get(route('tenant.tagihan.pay'));
        $page->assertOk();
        $page->assertSee('Ada 2 tagihan yang bisa dibayar.');
        $page->assertSee(e($this->tagihan->bill_number), false);
        $page->assertSee(e($tagihan2->bill_number), false);

        $response1 = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $this->tagihan->id,
            'amount' => 800000,
            'payment_method' => 'cash',
        ]);
        $response1->assertRedirect();
        $this->assertDatabaseHas('pembayarans', [
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'amount' => 800000,
        ]);

        $response2 = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $tagihan2->id,
            'amount' => 600000,
            'payment_method' => 'cash',
        ]);
        $response2->assertRedirect();
        $this->assertDatabaseHas('pembayarans', [
            'tagihan_id' => $tagihan2->id,
            'penghuni_id' => $penghuni2->id,
            'amount' => 600000,
        ]);
    }
}
