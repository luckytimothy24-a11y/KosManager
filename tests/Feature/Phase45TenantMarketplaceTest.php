<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase45TenantMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $tenant;

    protected Kos $kos;

    protected Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);

        $this->kos = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'active',
            'general_facilities' => 'WiFi; Parkir; Keamanan; Area Jemur',
            'payment_info' => 'Bank BCA 1234567890 a/n Pemilik Kos',
            'latitude' => -7.7956,
            'longitude' => 110.3695,
        ]);

        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'available',
            'monthly_price' => 900000,
        ]);
    }

    /** Kos Detail contains general facilities from database */
    public function test_kos_detail_shows_general_facilities(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));
        $response->assertOk();
        $response->assertSee('WiFi');
        $response->assertSee('Parkir');
        $response->assertSee('Keamanan');
        $response->assertSee('Area Jemur');
    }

    /** Kos detail shows payment info when available */
    public function test_kos_detail_shows_payment_info(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));
        $response->assertOk();
        $response->assertSee('Bank BCA');
        $response->assertSee('1234567890');
        $response->assertSee('Cara Pembayaran');
    }

    /** Kos detail hides payment info when empty */
    public function test_kos_detail_hides_payment_info_when_empty(): void
    {
        $this->kos->update(['payment_info' => null]);
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));
        $response->assertOk();
        $response->assertDontSee('Cara Pembayaran');
    }

    /** Room contains actual facilities from database */
    public function test_room_shows_facilities_from_database(): void
    {
        $facility = Fasilitas::factory()->create(['name' => 'AC']);
        $this->kamar->fasilitas()->attach($facility->id);

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));
        $response->assertOk();
        $response->assertSee('AC');
    }

    /** Cheapest available room price shown */
    public function test_cheapest_room_price_shown(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));
        $response->assertOk();
        $response->assertSee('Rp 900.000');
    }

    /** Unavailable room does not show booking CTA on its card */
    public function test_unavailable_room_no_booking_cta(): void
    {
        $kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'maintenance',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));
        $response->assertOk();
        $bookingUrl = route('tenant.booking.create', ['kos_id' => $this->kos->id, 'kamar_id' => $kamar->id]);
        $response->assertDontSee($bookingUrl);
    }

    /** Available room shows booking CTA */
    public function test_available_room_shows_booking_cta(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));
        $response->assertOk();
        $response->assertSee('Booking Sekarang');
    }

    /** Google Maps appears when coordinates exist */
    public function test_google_maps_shows_when_coordinates_exist(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));
        $response->assertOk();
        $response->assertSee('maps.google.com');
        $response->assertSee('Lokasi');
    }

    /** Google Maps fallback when coordinates missing */
    public function test_google_maps_fallback_when_no_coordinates(): void
    {
        $this->kos->update(['latitude' => null, 'longitude' => null]);
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));
        $response->assertOk();
        $response->assertDontSee('maps.google.com');
        $response->assertSee('Lokasi peta belum ditentukan oleh pengelola.');
    }

    /** Similar kos only active with available rooms */
    public function test_similar_kos_only_active_with_rooms(): void
    {
        $otherKos = Kos::factory()->create(['status' => 'active']);
        Kamar::factory()->create(['kos_id' => $otherKos->id, 'status' => 'available']);

        $inactiveKos = Kos::factory()->create(['status' => 'inactive']);
        Kamar::factory()->create(['kos_id' => $inactiveKos->id, 'status' => 'available']);

        $noRoomKos = Kos::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));
        $response->assertOk();
        $response->assertSee($otherKos->name);
        $response->assertDontSee($inactiveKos->name);
        $response->assertDontSee($noRoomKos->name);
    }

    /** Payment methods display correctly on tagihan */
    public function test_payment_methods_display_on_tagihan(): void
    {
        $penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kamar_id' => $this->kamar->id,
        ]);

        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'unpaid',
            'total' => 900000,
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $tagihan));
        $response->assertOk();
        $response->assertSee('Transfer Bank');
        $response->assertSee('E-Wallet / QRIS');
        $response->assertSee('Tunai');
    }

    /** Payment status unpaid shows correctly */
    public function test_payment_status_unpaid(): void
    {
        $penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kamar_id' => $this->kamar->id,
        ]);

        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $tagihan));
        $response->assertOk();
        $response->assertSee('Belum Dibayar');
    }

    /** Payment status pending_verification shows correctly */
    public function test_payment_status_pending_verification(): void
    {
        $penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kamar_id' => $this->kamar->id,
        ]);

        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'pending_verification',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $tagihan));
        $response->assertOk();
        $response->assertSee('Menunggu Verifikasi');
    }

    /** Payment status paid shows correctly */
    public function test_payment_status_paid(): void
    {
        $penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kamar_id' => $this->kamar->id,
        ]);

        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $tagihan));
        $response->assertOk();
        $response->assertSee('Tagihan Lunas');
    }

    /** Rejected payment shows reason on pembayaran show */
    public function test_rejected_payment_shows_reason(): void
    {
        $penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kamar_id' => $this->kamar->id,
        ]);

        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'unpaid',
        ]);

        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'verification_status' => 'rejected',
            'admin_notes' => 'Bukti tidak jelas',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.pembayaran.show', $pembayaran));
        $response->assertOk();
        $response->assertSee('Pembayaran Ditolak');
        $response->assertSee('Bukti tidak jelas');
    }

    /** Tenant payment isolation - cannot see other tenant proofs */
    public function test_tenant_cannot_see_other_tenant_payment(): void
    {
        $otherTenant = User::factory()->create(['role' => 'tenant']);
        $otherTenantKamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'available',
        ]);

        $otherPenghuni = Penghuni::factory()->create([
            'user_id' => $otherTenant->id,
            'kamar_id' => $otherTenantKamar->id,
        ]);

        $otherTagihan = Tagihan::factory()->create([
            'penghuni_id' => $otherPenghuni->id,
            'kamar_id' => $otherTenantKamar->id,
            'status' => 'pending_verification',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.tagihan.show', $otherTagihan));
        $response->assertForbidden();
    }

    /** Tenant booking isolation - cannot see other tenant booking */
    public function test_tenant_cannot_see_other_tenant_booking(): void
    {
        $otherTenant = User::factory()->create(['role' => 'tenant']);
        $otherKamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'booked',
        ]);

        $otherBooking = Booking::factory()->create([
            'user_id' => $otherTenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $otherKamar->id,
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.show', $otherBooking));
        $response->assertForbidden();
    }

    /** No fake rating on kos detail */
    public function test_no_fake_rating(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));
        $response->assertOk();
        $response->assertDontSee('rating');
        $response->assertDontSee('bintang');
        $response->assertDontSee('★');
        $response->assertDontSee('stars');
    }

    /** No fake review on kos detail */
    public function test_no_fake_review(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));
        $response->assertOk();
        $response->assertDontSee('review');
        $response->assertDontSee('ulasan');
        $response->assertDontSee('testimoni');
    }

    /** No fake distance on kos detail */
    public function test_no_fake_distance(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));
        $response->assertOk();
        $response->assertDontSee('km dari');
        $response->assertDontSee('meter dari');
    }

    /** Booking success shows booking code */
    public function test_booking_success_shows_booking_code(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.show', $booking));
        $response->assertOk();
        $response->assertSee($booking->booking_code);
        $response->assertSee('Booking sudah terkonfirmasi');
    }

    /** Mobile CTA exists on kos detail */
    public function test_mobile_sticky_cta_exists(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));
        $response->assertOk();
        $response->assertSee('Booking Sekarang');
        $response->assertSee('Mulai dari');
    }

    /** No N+1 regression - kos index loads efficiently */
    public function test_kos_index_no_n_plus_one(): void
    {
        Kos::factory()->count(3)->create(['status' => 'active'])->each(function ($kos) {
            Kamar::factory()->count(2)->create(['kos_id' => $kos->id, 'status' => 'available']);
        });

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.index'));
        $response->assertOk();
    }

    /** Kos detail has trust section */
    public function test_kos_detail_has_trust_section(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));
        $response->assertOk();
        $response->assertSee('Kenapa Booking di KosManager?');
        $response->assertSee('Booking langsung terkonfirmasi');
        $response->assertSee('Harga ditampilkan sebelum booking');
        $response->assertSee('Data pembayaran aman');
    }

    /** Kos rules section exists */
    public function test_kos_detail_has_rules_section(): void
    {
        $this->kos->update(['rules' => 'Dilarang merokok. Jam malam pukul 22.00.']);

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));
        $response->assertOk();
        $response->assertSee('Peraturan');
        $response->assertSee('Dilarang merokok');
    }

    /** Room detail modal has escape support */
    public function test_room_detail_modal_has_alpine(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $this->kos));
        $response->assertOk();
        $response->assertSee('detailKamar');
        $response->assertSee('x-show');
    }
}
