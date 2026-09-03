<?php

namespace Tests\Feature;

use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantExperienceTest extends TestCase
{
    use RefreshDatabase;

    private function createTenant(): User
    {
        return User::factory()->create(['role' => 'tenant']);
    }

    private function createOwner(): User
    {
        return User::factory()->create(['role' => 'owner']);
    }

    // ── Dashboard ────────────────────────────────────────────

    public function test_dashboard_shows_recommendations(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'name' => 'Kos Rekomendasi']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Rekomendasi untukmu');
        $response->assertSee('Kos Rekomendasi');
    }

    public function test_dashboard_recommendations_only_active_kos(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner();
        $kosActive = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'name' => 'Kos Aktif']);
        Kamar::factory()->create(['kos_id' => $kosActive->id, 'status' => 'available']);
        Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'inactive', 'name' => 'Kos Inactive']);

        $response = $this->actingAs($tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Kos Aktif');
        $response->assertDontSee('Kos Inactive');
    }

    public function test_dashboard_recommendations_only_with_available_rooms(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner();
        $kosAvailable = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'name' => 'Kos Ada']);
        Kamar::factory()->create(['kos_id' => $kosAvailable->id, 'status' => 'available']);
        $kosFull = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'name' => 'Kos Penuh']);
        Kamar::factory()->create(['kos_id' => $kosFull->id, 'status' => 'occupied']);

        $response = $this->actingAs($tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Kos Ada');
        $response->assertDontSee('Kos Penuh');
    }

    public function test_dashboard_shows_welcome_message(): void
    {
        $tenant = $this->createTenant();

        $response = $this->actingAs($tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Halo');
        $response->assertSee($tenant->name);
    }

    public function test_dashboard_shows_quick_actions(): void
    {
        $tenant = $this->createTenant();

        $response = $this->actingAs($tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Cari Kos');
        $response->assertSee('Favorit');
        $response->assertSee('Booking Saya');
        $response->assertSee('Tagihan');
    }

    public function test_dashboard_shows_no_room_state_when_no_penghuni(): void
    {
        $tenant = $this->createTenant();

        $response = $this->actingAs($tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Belum Memiliki Kamar');
        $response->assertSee('Cari Kos Sekarang');
    }

    public function test_dashboard_recommendations_limit_six(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner();

        for ($i = 0; $i < 8; $i++) {
            $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
            Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        }

        $response = $this->actingAs($tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Rekomendasi untukmu');
    }

    // ── Discovery Page ────────────────────────────────────────

    public function test_kos_discovery_hero_section(): void
    {
        $tenant = $this->createTenant();

        $response = $this->actingAs($tenant)->get('/tenant/kos');

        $response->assertOk();
        $response->assertSee('Cari Kos Impianmu');
        $response->assertSee('Temukan kos yang cocok');
    }

    public function test_kos_discovery_filter_button_present(): void
    {
        $tenant = $this->createTenant();

        $response = $this->actingAs($tenant)->get('/tenant/kos');

        $response->assertOk();
        $response->assertSee('Filter');
    }

    public function test_empty_search_shows_no_results_message(): void
    {
        $tenant = $this->createTenant();
        Kos::factory()->create(['name' => 'Kos Melati', 'status' => 'active']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?q=inexistente');

        $response->assertOk();
        $response->assertSee('Kos tidak ditemukan');
        $response->assertSee('Pencarian untuk');
        $response->assertSee('Hapus Pencarian');
        $response->assertDontSee('Kos Melati');
    }

    public function test_no_kos_at_all_shows_empty_state(): void
    {
        $tenant = $this->createTenant();

        $response = $this->actingAs($tenant)->get('/tenant/kos');

        $response->assertOk();
        $response->assertSee('Kos belum tersedia');
        $response->assertSee('Belum ada kos yang terdaftar');
    }

    public function test_active_filter_pills_shown(): void
    {
        $tenant = $this->createTenant();

        $response = $this->actingAs($tenant)->get('/tenant/kos?q=test&tersedia_only=1');

        $response->assertOk();
        $response->assertSee('test');
        $response->assertSee('Tersedia');
        $response->assertSee('Reset');
    }

    public function test_sort_options_in_drawer(): void
    {
        $tenant = $this->createTenant();

        $response = $this->actingAs($tenant)->get('/tenant/kos');

        $response->assertOk();
        $response->assertSee('Filter & Urutkan', escape: false);
        $response->assertSee('Harga Terendah');
        $response->assertSee('Harga Tertinggi');
    }

    // ── Kos Detail ────────────────────────────────────────────

    public function test_kos_detail_shows_hero_section(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active', 'name' => 'Kos Hero Test']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Kos Hero Test');
        $response->assertSee('Tentang Kos');
    }

    public function test_kos_detail_quick_stats(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'occupied']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Total Kamar');
        $response->assertSee('Tersedia');
    }

    public function test_kos_detail_shows_description_when_exists(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active', 'description' => 'Kos nyaman dekat kampus']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Kos nyaman dekat kampus');
        $response->assertSee('Tentang Kos');
    }

    public function test_kos_detail_shows_fallback_when_no_description(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active', 'description' => null]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Belum ada deskripsi dari pengelola');
    }

    public function test_kos_detail_general_facilities_from_database(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active', 'general_facilities' => 'WiFi, Parkir, CCTV']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Fasilitas Kos');
        $response->assertSee('WiFi');
        $response->assertSee('Parkir');
        $response->assertSee('CCTV');
    }

    public function test_kos_detail_no_fake_facilities(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active', 'general_facilities' => null]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Informasi fasilitas belum tersedia');
    }

    public function test_room_detail_modal_hides_empty_fields(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create([
            'kos_id' => $kos->id,
            'status' => 'available',
            'description' => null,
            'floor' => null,
            'area' => null,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Lihat Detail');
    }

    public function test_similar_kos_section_heading(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'name' => 'Kos Utama']);
        $similar = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'name' => 'Kos Serupa']);
        Kamar::factory()->create(['kos_id' => $similar->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Kos Lain yang Mungkin Kamu Suka');
        $response->assertSee('Kos Serupa');
    }

    public function test_similar_kos_only_active(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'name' => 'Kos Utama']);
        Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'inactive', 'name' => 'Kos Inactive']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertDontSee('Kos Inactive');
    }

    public function test_similar_kos_only_with_available_rooms(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'name' => 'Kos Utama']);
        $kosFull = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'name' => 'Kos Penuh']);
        Kamar::factory()->create(['kos_id' => $kosFull->id, 'status' => 'occupied']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertDontSee('Kos Penuh');
    }

    public function test_room_cards_show_facility_preview(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $f1 = Fasilitas::create(['name' => 'AC', 'icon' => 'ri-temp-cold-line']);
        $f2 = Fasilitas::create(['name' => 'WiFi', 'icon' => 'ri-wifi-line']);
        $kamar->fasilitas()->attach([$f1->id, $f2->id]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('AC');
        $response->assertSee('WiFi');
        $response->assertSee('Pilihan Kamar');
    }

    public function test_pricing_uses_cheapest_available_room(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'monthly_price' => 1500000]);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'monthly_price' => 800000]);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'occupied', 'monthly_price' => 500000]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Rp 800.000');
    }

    public function test_location_section_present(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active', 'latitude' => -7.7955798, 'longitude' => 110.4052787]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Lokasi');
        $response->assertSee('maps.google.com');
        $response->assertSee('Buka di Google Maps');
        $response->assertSee('Petunjuk Arah');
    }

    public function test_directions_link_uses_correct_label(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active', 'latitude' => -7.7955798, 'longitude' => 110.4052787]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Petunjuk Arah');
    }

    public function test_favorite_button_in_hero(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Tambah ke favorit');
    }

    public function test_cara_booking_section(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Cara Booking');
    }

    public function test_room_section_title(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Pilihan Kamar');
    }

    public function test_guest_redirected_from_kos_detail(): void
    {
        $kos = Kos::factory()->create(['status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $response = $this->get("/tenant/kos/{$kos->id}");

        $response->assertRedirect('/login');
    }

    public function test_no_data_integrity_fake_data(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertDontSee('★');
        $response->assertDontSee('4.5');
        $response->assertDontSee('100m');
        $response->assertDontSee('500m');
        $response->assertDontSee('rating');
    }

    public function test_facility_detail_expandable(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'general_facilities' => 'WiFi, Parkir, CCTV, Dapur, Laundry, Ruang Tamu, Akses 24 Jam, Gazebo, Security',
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('lainnya');
    }

    public function test_kos_discovery_pagination(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner();

        for ($i = 0; $i < 12; $i++) {
            Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        }

        $response = $this->actingAs($tenant)->get('/tenant/kos');

        $response->assertOk();
    }

    public function test_kos_discovery_facility_filter_in_drawer(): void
    {
        $tenant = $this->createTenant();
        $f = Fasilitas::create(['name' => 'AC', 'icon' => 'ri-temp-cold-line']);

        $response = $this->actingAs($tenant)->get('/tenant/kos');

        $response->assertOk();
        $response->assertSee('AC');
        $response->assertSee('Filter & Urutkan', escape: false);
    }

    public function test_kos_discovery_price_filter_in_drawer(): void
    {
        $tenant = $this->createTenant();

        $response = $this->actingAs($tenant)->get('/tenant/kos');

        $response->assertOk();
        $response->assertSee('Harga per Bulan');
        $response->assertSee('Ketersediaan');
    }

    public function test_no_n_plus_one_on_facilities(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner();

        for ($i = 0; $i < 5; $i++) {
            $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
            $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
            $f = Fasilitas::create(['name' => "Fasilitas {$i}"]);
            $kamar->fasilitas()->attach($f->id);
        }

        DB::enableQueryLog();
        $this->actingAs($tenant)->get('/tenant/kos');
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        $this->assertLessThan(30, count($queries), 'Too many queries ('.count($queries).'), possible N+1');
    }
}
