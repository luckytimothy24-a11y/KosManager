<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Fasilitas;
use App\Models\Favorite;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase46TenantMarketplaceUltimateTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
    }

    private function kosWithAvailableRoom(string $name): Kos
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'name' => $name]);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        return $kos;
    }

    // ── Phase 1 · Marketplace homepage: welcome, search, kos terbaru ──

    public function test_marketplace_dashboard_shows_welcome_and_search(): void
    {
        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Halo');
        $response->assertSee($this->tenant->name);
        $response->assertSee('Cari Kos');
        $response->assertSee('Semua Kos');
    }

    public function test_marketplace_dashboard_shows_kos_terbaru_section(): void
    {
        $this->kosWithAvailableRoom('Kos Terbaru A');
        $this->kosWithAvailableRoom('Kos Terbaru B');

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Kos Terbaru');
        $response->assertSee('Kos Terbaru A');
        $response->assertSee('Kos Terbaru B');
    }

    public function test_dashboard_sections_only_active_with_available_rooms(): void
    {
        $this->kosWithAvailableRoom('Kos Tampil');
        Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'inactive', 'name' => 'Kos Nonaktif']);
        Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'name' => 'Kos Penuh']);

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Kos Tampil');
        $response->assertDontSee('Kos Nonaktif');
        $response->assertDontSee('Kos Penuh');
    }

    // ── Phase 10 · Kos Terbaru capped at six ──

    public function test_kos_terbaru_dashboard_capped_at_six(): void
    {
        // 7 kos dengan waktu pembuatan berbeda agar urutan deterministik.
        for ($i = 0; $i < 7; $i++) {
            $kos = Kos::factory()->create([
                'owner_id' => $this->owner->id,
                'status' => 'active',
                'name' => $i === 0 ? 'Kos Terlama' : 'Kos Terbaru '.$i,
                'created_at' => now()->subDays(7 - $i),
            ]);
            Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        }

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        // 6 kos terbaru tampil, kos paling lama tidak.
        foreach ([1, 2, 3, 4, 5, 6] as $i) {
            $response->assertSee('Kos Terbaru '.$i, false);
        }
        $response->assertDontSee('Kos Terlama', false);
    }

    // ── Phase 11 · Data-backed availability badge (no fake data) ──

    public function test_dashboard_kos_card_shows_data_backed_availability_badge(): void
    {
        $this->kosWithAvailableRoom('Kos Tersedia');

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Kos Tersedia');
        $response->assertSee('TERSEDIA');
    }

    // ── Phase 8 · Favorites section on dashboard ──

    public function test_dashboard_favorites_section_shows_saved_kos(): void
    {
        $favKos = $this->kosWithAvailableRoom('Kos Favorit Saya');
        Favorite::create(['user_id' => $this->tenant->id, 'kos_id' => $favKos->id]);

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Kos yang Kamu Simpan');
        $response->assertSee('Kos Favorit Saya');
        $response->assertSee('(1)');
    }

    public function test_dashboard_favorites_section_empty_state(): void
    {
        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Kos yang Kamu Simpan');
        $response->assertSee('Belum ada kos yang disimpan.');
        $response->assertSee('Cari Kos');
    }

    public function test_dashboard_favorites_limited_to_four(): void
    {
        $favs = [
            $this->kosWithAvailableRoom('Kos Fav 1'),
            $this->kosWithAvailableRoom('Kos Fav 2'),
            $this->kosWithAvailableRoom('Kos Fav 3'),
            $this->kosWithAvailableRoom('Kos Fav 4'),
        ];

        // Kos ke-5 tidak punya kamar tersedia; favorit tapi tertua → harus tidak tampil.
        $kosPenuh = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'name' => 'Kos Fav 5']);

        foreach ([$kosPenuh, ...$favs] as $i => $k) {
            Favorite::create(['user_id' => $this->tenant->id, 'kos_id' => $k->id]);
        }

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Kos Fav 1');
        $response->assertSee('Kos Fav 2');
        $response->assertSee('Kos Fav 3');
        $response->assertSee('Kos Fav 4');
        $response->assertDontSee('Kos Fav 5');
    }

    // ── Phase 13 · Recently viewed (localStorage) ──

    public function test_dashboard_has_recently_viewed_section(): void
    {
        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Baru Kamu Lihat');
        $response->assertSee('kosmanager:recently_viewed');
    }

    public function test_kos_detail_records_recently_viewed(): void
    {
        $kos = $this->kosWithAvailableRoom('Kos Detail Riwayat');

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $kos));

        $response->assertOk();
        $response->assertSee('record-view');
    }

    // ── Phase 2 · Quick filter chips from real facilities ──

    public function test_kos_index_quick_filter_chips_use_real_facilities(): void
    {
        Fasilitas::factory()->create(['name' => 'Parkir']);
        Fasilitas::factory()->create(['name' => 'AC']);
        $this->kosWithAvailableRoom('Kos Disaring');

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.index'));

        $response->assertOk();
        $response->assertSee('Parkir');
        $response->assertSee('AC');
        $response->assertSee('kos ditemukan');
    }

    // ── Phase 7 · Booking success page ──

    public function test_booking_success_page_shows_confirmation_and_ctas(): void
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.success', $booking));

        $response->assertOk();
        $response->assertSee('Booking Berhasil!');
        $response->assertSee('🎉');
        $response->assertSee('Terkonfirmasi');
        $response->assertSee($booking->booking_code);
        $response->assertSee('Lihat Booking');
        $response->assertSee('Lihat Detail Kos');
        $response->assertSee('Kembali Cari Kos');
    }

    public function test_booking_success_isolated_per_tenant(): void
    {
        $otherTenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $booking = Booking::factory()->create([
            'user_id' => $otherTenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.success', $booking));

        $response->assertForbidden();
    }

    // ── Phase 14 · Mobile bottom navigation ──

    public function test_mobile_bottom_nav_has_core_links(): void
    {
        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('aria-label="Navigasi utama"', false);
        $response->assertSee(route('dashboard'), false);
        $response->assertSee(route('tenant.kos.index'), false);
        $response->assertSee(route('tenant.favorites.index'), false);
        $response->assertSee(route('tenant.booking.index'), false);
        $response->assertSee(route('tenant.tagihan.index'), false);
        $response->assertSee(route('profile.edit'), false);
        $response->assertSee('Beranda');
        $response->assertSee('Cari');
        $response->assertSee('Favorit');
        $response->assertSee('Booking');
        $response->assertSee('Tagihan');
        $response->assertSee('Profil');
    }

    // ── Phase 15 · No fake marketing data on the marketplace ──

    public function test_marketplace_has_no_fake_metrics(): void
    {
        $this->kosWithAvailableRoom('Kos Jujur');
        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('kali disewa');
        $response->assertDontSee('terbanyak disewa');
        $response->assertDontSee('★★★★★');
        $response->assertDontSee('rating 5');
        $response->assertDontSee('km dari');
        $response->assertDontSee('meter dari');
    }

    // ── Phase 4.7 · Visual polish & refined marketplace UI ──

    public function test_marketplace_dashboard_shows_hero_shortcuts(): void
    {
        $this->kosWithAvailableRoom('Kos Shortcut');

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Atau cari cepat');
        $response->assertSee('Harga Terjangkau');
        $response->assertSee(route('tenant.kos.index', ['sort' => 'harga_terendah']), false);
        $response->assertSee(route('tenant.kos.index', ['tersedia_only' => 1]), false);
        $response->assertSee('Tersedia');
    }

    public function test_marketplace_dashboard_has_compact_quick_actions(): void
    {
        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('aria-label="Aksi cepat"', false);
        $response->assertSee(route('tenant.booking.index'), false);
        $response->assertSee(route('tenant.tagihan.index'), false);
        $response->assertSee('Booking', false);
        $response->assertSee('Tagihan', false);
    }

    public function test_marketplace_has_faq_and_final_cta(): void
    {
        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Masih bingung?');
        $response->assertSee('Bagaimana cara booking?');
        $response->assertSee('Bagaimana cara membayar?');
        $response->assertSee('Sudah menemukan kos yang cocok?');
        $response->assertSee('Pesan kamar pilihanmu sekarang.');
    }

    public function test_tenant_sidebar_uses_utama_aktivitas_groups(): void
    {
        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Utama');
        $response->assertSee('Aktivitas');
        // No TEMPAT TINGGAL group since there are no tenant kontrak/check-in/kos-saya index routes
        $response->assertDontSee('Tempat Tinggal');
        $response->assertDontSee('tenant.kontrak', false);
        $response->assertDontSee('tenant.checkin', false);
    }

    public function test_non_tenant_sidebar_keeps_dashboard_at_top(): void
    {
        $response = $this->actingAs($this->owner)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard');
    }
}
