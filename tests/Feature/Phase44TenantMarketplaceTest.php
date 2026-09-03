<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Fasilitas;
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

class Phase44TenantMarketplaceTest extends TestCase
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

    // ── Payment Flow ────────────────────────────────────────

    public function test_tagihan_show_displays_bill_info(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertSee($this->tagihan->bill_number);
        $response->assertSee('Rp 800.000');
        $response->assertSee($this->kamar->room_number);
        $response->assertSee($this->kos->name);
        $response->assertSee('Belum Dibayar');
    }

    public function test_tagihan_show_shows_payment_info_when_exists(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertSee('BCA 1234567890');
        $response->assertSee('Instruksi Pembayaran');
    }

    public function test_tagihan_show_hides_payment_info_when_empty(): void
    {
        $this->kos->update(['payment_info' => null]);
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertDontSee('Instruksi Pembayaran');
    }

    public function test_tagihan_show_displays_payment_form_for_unpaid(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertSee('Bayar Tagihan Ini');
        $response->assertSee('Transfer Bank');
        $response->assertSee('E-Wallet / QRIS');
        $response->assertSee('Tunai');
        $response->assertSee('Kirim Pembayaran');
        $response->assertSee('Bukti Pembayaran');
    }

    public function test_tagihan_show_hides_payment_form_for_paid(): void
    {
        $this->tagihan->update(['status' => 'paid']);
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertDontSee('Bayar Tagihan Ini');
        $response->assertDontSee('Kirim Pembayaran');
        $response->assertSee('Tagihan Lunas');
    }

    public function test_tagihan_show_hides_payment_form_for_pending_verification(): void
    {
        $this->tagihan->update(['status' => 'pending_verification']);
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertDontSee('Bayar Tagihan Ini');
        $response->assertDontSee('Kirim Pembayaran');
        $response->assertSee('Menunggu Verifikasi');
    }

    public function test_tagihan_show_displays_overdue_status(): void
    {
        $this->tagihan->update(['status' => 'overdue']);
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertSee('Terlambat');
    }

    public function test_tagihan_index_displays_bills(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.index'));

        $response->assertOk();
        $response->assertSee($this->tagihan->bill_number);
        $response->assertSee('Bayar Sekarang');
    }

    // ── QRIS / Payment Methods ─────────────────────────────

    public function test_payment_method_e_wallet_label_exists(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertSee('e_wallet');
        $response->assertSee('E-Wallet', escape: false);
    }

    public function test_payment_method_transfer_bank_exists(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertSee('transfer_bank');
    }

    public function test_payment_method_cash_exists(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertSee('cash');
    }

    public function test_qris_not_shown_as_standalone_method(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertOk();
        $response->assertDontSee('value="qris"');
    }

    // ── Payment Proof Upload ────────────────────────────────

    public function test_tenant_can_upload_payment_proof(): void
    {
        $proof = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');

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
            'amount' => 800000,
            'payment_method' => 'transfer_bank',
            'verification_status' => 'pending',
        ]);
        $this->assertDatabaseHas('tagihans', [
            'id' => $this->tagihan->id,
            'status' => 'pending_verification',
        ]);
    }

    public function test_payment_success_shows_correct_message(): void
    {
        $proof = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $this->tagihan->id,
            'amount' => 800000,
            'payment_method' => 'transfer_bank',
            'proof_file' => $proof,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertStringContainsString('Bukti pembayaran berhasil', session('success'));
    }

    public function test_wrong_amount_rejected(): void
    {
        $proof = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $this->tagihan->id,
            'amount' => 500000,
            'payment_method' => 'transfer_bank',
            'proof_file' => $proof,
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_cannot_pay_already_paid_tagihan(): void
    {
        $this->tagihan->update(['status' => 'paid']);
        $proof = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $this->tagihan->id,
            'amount' => 800000,
            'payment_method' => 'transfer_bank',
            'proof_file' => $proof,
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_pending_verification_blocks_double_payment(): void
    {
        $this->tagihan->update(['status' => 'pending_verification']);
        Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'verification_status' => 'pending',
        ]);

        $proof = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $this->tagihan->id,
            'amount' => 800000,
            'payment_method' => 'transfer_bank',
            'proof_file' => $proof,
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_all_three_payment_methods_valid(): void
    {
        foreach (['transfer_bank', 'e_wallet', 'cash'] as $method) {
            $tagihan = Tagihan::factory()->create([
                'penghuni_id' => $this->penghuni->id,
                'kontrak_id' => $this->kontrak->id,
                'kamar_id' => $this->kamar->id,
                'status' => 'unpaid',
                'total' => 500000,
            ]);

            $proof = $method !== 'cash'
                ? UploadedFile::fake()->create("bukti_{$method}.pdf", 100, 'application/pdf')
                : null;

            $data = [
                'tagihan_id' => $tagihan->id,
                'amount' => 500000,
                'payment_method' => $method,
            ];
            if ($proof) {
                $data['proof_file'] = $proof;
            }

            $response = $this->actingAs($this->tenant)->post(route('tenant.pembayaran.store'), $data);

            $response->assertRedirect();
            $this->assertDatabaseHas('pembayarans', [
                'tagihan_id' => $tagihan->id,
                'payment_method' => $method,
            ]);
        }
    }

    // ── Payment Verification ────────────────────────────────

    public function test_pembayaran_show_displays_pending_status(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.pembayaran.show', $pembayaran));

        $response->assertOk();
        $response->assertSee('Menunggu Verifikasi');
        $response->assertSee('Bukti Pembayaran Terkirim');
    }

    public function test_pembayaran_show_displays_approved_status(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'verification_status' => 'approved',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.pembayaran.show', $pembayaran));

        $response->assertOk();
        $response->assertSee('Terverifikasi');
    }

    public function test_pembayaran_show_displays_rejected_status(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'verification_status' => 'rejected',
            'admin_notes' => 'Bukti tidak jelas',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.pembayaran.show', $pembayaran));

        $response->assertOk();
        $response->assertSee('Pembayaran Ditolak');
        $response->assertSee('Bukti tidak jelas');
    }

    public function test_rejected_payment_shows_repay_option(): void
    {
        $this->tagihan->update(['status' => 'unpaid']);
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'verification_status' => 'rejected',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.pembayaran.show', $pembayaran));

        $response->assertOk();
        $response->assertSee('Bayar Kembali');
    }

    public function test_rejected_payment_hides_repay_when_paid(): void
    {
        $this->tagihan->update(['status' => 'paid']);
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'verification_status' => 'rejected',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.pembayaran.show', $pembayaran));

        $response->assertOk();
        $response->assertDontSee('Bayar Kembali');
    }

    // ── Tenant Isolation ────────────────────────────────────

    public function test_tenant_cannot_see_other_tenant_tagihan(): void
    {
        $response = $this->actingAs($this->otherTenant)->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertForbidden();
    }

    public function test_tenant_cannot_pay_other_tenant_tagihan(): void
    {
        $proof = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->otherTenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $this->tagihan->id,
            'amount' => 800000,
            'payment_method' => 'transfer_bank',
            'proof_file' => $proof,
        ]);

        $response->assertForbidden();
    }

    public function test_tenant_cannot_see_other_tenant_pembayaran(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($this->otherTenant)->get(route('tenant.pembayaran.show', $pembayaran));

        $response->assertForbidden();
    }

    public function test_guest_redirected_from_tagihan(): void
    {
        $response = $this->get(route('tenant.tagihan.show', $this->tagihan));

        $response->assertRedirect('/login');
    }

    public function test_guest_redirected_from_pembayaran(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
        ]);

        $response = $this->get(route('tenant.pembayaran.show', $pembayaran));

        $response->assertRedirect('/login');
    }

    // ── Booking Flow ────────────────────────────────────────

    public function test_booking_create_shows_kos_list(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.create'));

        $response->assertOk();
        $response->assertSee($this->kos->name);
        $response->assertSee('Pilih Kos');
    }

    public function test_booking_create_shows_rooms_when_kos_selected(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.create', ['kos_id' => $this->kos->id]));

        $response->assertOk();
        $response->assertSee($this->kamar->room_number);
        $response->assertSee('800.000');
    }

    public function test_booking_create_shows_trust_info(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.create', ['kos_id' => $this->kos->id]));

        $response->assertOk();
        $response->assertSee('Booking langsung terkonfirmasi');
        $response->assertSee('Kamar langsung diamankan');
        $response->assertSee('Pembayaran dilakukan sesuai tagihan');
        $response->assertSee('Check-in diproses pengelola saat kedatangan');
    }

    public function test_booking_create_shows_review_summary(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.create', ['kos_id' => $this->kos->id]));

        $response->assertOk();
        $response->assertSee('Review Booking');
        $response->assertSee('Konfirmasi Booking');
    }

    public function test_booking_create_shows_empty_when_no_rooms(): void
    {
        $this->kamar->update(['status' => 'occupied']);

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.create', ['kos_id' => $this->kos->id]));

        $response->assertOk();
        $response->assertSee('Tidak ada kamar tersedia');
    }

    public function test_booking_show_displays_status_timeline(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.show', $booking));

        $response->assertOk();
        $response->assertSee('Status Booking');
        $response->assertSee('Booking Dibuat');
        $response->assertSee('Booking Dikonfirmasi');
    }

    public function test_booking_index_shows_bookings(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.index'));

        $response->assertOk();
        $response->assertSee($booking->booking_code);
    }

    // ── Kos Detail ──────────────────────────────────────────

    public function test_kos_detail_shows_all_sections(): void
    {
        $f = Fasilitas::create(['name' => 'WiFi']);
        $this->kamar->fasilitas()->attach($f->id);

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));

        $response->assertOk();
        $response->assertSee('Tentang Kos');
        $response->assertSee('Fasilitas Kos');
        $response->assertSee('Pilihan Kamar');
        $response->assertSee('Lokasi');
        $response->assertSee('Cara Booking');
    }

    public function test_kos_detail_shows_room_facilities_from_db(): void
    {
        $f1 = Fasilitas::create(['name' => 'AC']);
        $f2 = Fasilitas::create(['name' => 'WiFi']);
        $this->kamar->fasilitas()->attach([$f1->id, $f2->id]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));

        $response->assertOk();
        $response->assertSee('AC');
        $response->assertSee('WiFi');
    }

    public function test_kos_detail_shows_general_facilities(): void
    {
        $this->kos->update(['general_facilities' => 'WiFi;Parkir;CCTV;Dapur']);
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));

        $response->assertOk();
        $response->assertSee('Fasilitas Kos');
        $response->assertSee('WiFi');
        $response->assertSee('Parkir');
    }

    public function test_kos_detail_shows_cheapest_available_price(): void
    {
        $kamar2 = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'available', 'monthly_price' => 500000]);
        $kamar3 = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'occupied', 'monthly_price' => 300000]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));

        $response->assertOk();
        $response->assertSee('500.000');
    }

    public function test_kos_detail_shows_description_when_exists(): void
    {
        $this->kos->update(['description' => 'Kos nyaman dekat kampus']);
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));

        $response->assertOk();
        $response->assertSee('Kos nyaman dekat kampus');
    }

    public function test_kos_detail_shows_fallback_when_no_description(): void
    {
        $this->kos->update(['description' => null]);
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));

        $response->assertOk();
        $response->assertSee('Belum ada deskripsi');
    }

    public function test_kos_detail_shows_owner_info(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));

        $response->assertOk();
        $response->assertSee('Informasi Pemilik');
        $response->assertSee($this->owner->name);
    }

    public function test_kos_detail_shows_rules_when_exists(): void
    {
        $this->kos->update(['rules' => 'Dilarang merokok. Jam malam 22:00.']);
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));

        $response->assertOk();
        $response->assertSee('Peraturan');
        $response->assertSee('Dilarang merokok');
    }

    public function test_kos_detail_shows_room_cards(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));

        $response->assertOk();
        $response->assertSee($this->kamar->room_number);
        $response->assertSee('Tersedia');
    }

    public function test_kos_detail_shows_google_maps_when_coords_exist(): void
    {
        $this->kos->update(['latitude' => -7.7956, 'longitude' => 110.3695]);
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));

        $response->assertOk();
        $response->assertSee('maps.google.com');
        $response->assertSee('Buka di Google Maps');
        $response->assertSee('Petunjuk Arah');
    }

    public function test_kos_detail_shows_fallback_when_no_coords(): void
    {
        $this->kos->update(['latitude' => null, 'longitude' => null]);
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));

        $response->assertOk();
        $response->assertSee('Lokasi peta belum ditentukan');
    }

    public function test_kos_detail_favorite_button(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));

        $response->assertOk();
        $response->assertSee('Tambahkan ke Favorit');
        $response->assertSee(route('tenant.favorites.toggle'));
    }

    public function test_kos_detail_similar_kos(): void
    {
        $kos2 = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos2->id, 'status' => 'available']);

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));

        $response->assertOk();
        $response->assertSee('Kos Lain yang Mungkin Kamu Suka');
    }

    public function test_kos_detail_no_fake_data(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));

        $response->assertOk();
        $response->assertDontSee('5.0');
        $response->assertDontSee('rating');
        $response->assertDontSee('terbaik');
    }

    // ── Mobile UX ──────────────────────────────────────────

    public function test_all_tenant_pages_render(): void
    {
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $this->tagihan->id,
            'penghuni_id' => $this->penghuni->id,
        ]);

        $pages = [
            '/dashboard',
            route('tenant.kos.index'),
            route('tenant.kos.show', $this->kos),
            route('tenant.tagihan.index'),
            route('tenant.tagihan.show', $this->tagihan),
            route('tenant.pembayaran.index'),
            route('tenant.pembayaran.show', $pembayaran),
            route('tenant.booking.index'),
        ];

        foreach ($pages as $page) {
            $response = $this->actingAs($this->tenant)->get($page);
            $response->assertOk();
        }
    }

    // ── Dashboard ──────────────────────────────────────────

    public function test_dashboard_shows_tagihan_count(): void
    {
        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Tagihan Belum Bayar');
    }

    public function test_dashboard_shows_booking_link(): void
    {
        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Booking Saya');
    }
}
