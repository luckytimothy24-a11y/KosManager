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

class PhaseETenantMarketplaceTest extends TestCase
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

    private function kos(string $name = 'Kos E', string $address = 'Jl. E No. 1'): Kos
    {
        return Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'name' => $name,
            'address' => $address,
            'status' => 'active',
        ]);
    }

    private function kamar(Kos $kos, string $status = 'available', ?int $price = 1000000): Kamar
    {
        return Kamar::factory()->create([
            'kos_id' => $kos->id,
            'status' => $status,
            'monthly_price' => $price,
            'daily_price' => $price / 30,
        ]);
    }

    // ── Favorites: correct state, toggle, persistence, isolation ──

    public function test_favorite_toggle_creates_favorite(): void
    {
        $kos = $this->kos();

        $response = $this->actingAs($this->tenant)->post(route('tenant.favorites.toggle'), ['kos_id' => $kos->id]);

        $response->assertRedirect();
        $this->assertDatabaseHas('favorites', ['user_id' => $this->tenant->id, 'kos_id' => $kos->id]);
    }

    public function test_favorite_toggle_removes_favorite(): void
    {
        $kos = $this->kos();
        Favorite::create(['user_id' => $this->tenant->id, 'kos_id' => $kos->id]);

        $this->actingAs($this->tenant)->post(route('tenant.favorites.toggle'), ['kos_id' => $kos->id]);

        $this->assertDatabaseMissing('favorites', ['user_id' => $this->tenant->id, 'kos_id' => $kos->id]);
    }

    public function test_favorite_state_is_accurate_on_listing(): void
    {
        $kos = $this->kos('Kos Fav');
        $this->kamar($kos, 'available');
        Favorite::create(['user_id' => $this->tenant->id, 'kos_id' => $kos->id]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.index'));

        $response->assertOk();
        $response->assertSee('aria-label="Hapus dari favorit"', false);
    }

    public function test_favorite_state_is_accurate_on_detail(): void
    {
        $kos = $this->kos('Kos Fav Detail');
        $this->kamar($kos, 'available');
        Favorite::create(['user_id' => $this->tenant->id, 'kos_id' => $kos->id]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $kos));

        $response->assertOk();
        $response->assertSee('Hapus dari Favorit');
    }

    public function test_favorite_persists_after_refresh(): void
    {
        $kos = $this->kos();
        $this->kamar($kos, 'available');

        $this->actingAs($this->tenant)->post(route('tenant.favorites.toggle'), ['kos_id' => $kos->id]);
        $this->actingAs($this->tenant)->post(route('tenant.favorites.toggle'), ['kos_id' => $kos->id]);

        $this->assertDatabaseMissing('favorites', ['user_id' => $this->tenant->id, 'kos_id' => $kos->id]);
    }

    public function test_favorite_toggle_state_persists_after_reload(): void
    {
        $kos = $this->kos('Kos Reload');
        $this->kamar($kos, 'available');
        $this->actingAs($this->tenant)->post(route('tenant.favorites.toggle'), ['kos_id' => $kos->id]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.favorites.index'));

        $response->assertOk();
        $response->assertSee('Kos Reload');
        $response->assertSee('Hapus dari favorit');
    }

    public function test_favorite_tenant_isolation(): void
    {
        $tenantA = User::factory()->create(['role' => 'tenant']);
        $tenantB = User::factory()->create(['role' => 'tenant']);
        $kos = $this->kos('Kos Isolasi');
        $this->kamar($kos, 'available');

        $this->actingAs($tenantA)->post(route('tenant.favorites.toggle'), ['kos_id' => $kos->id]);

        $this->assertDatabaseHas('favorites', ['user_id' => $tenantA->id, 'kos_id' => $kos->id]);
        $this->assertDatabaseMissing('favorites', ['user_id' => $tenantB->id, 'kos_id' => $kos->id]);
    }

    public function test_favorites_index_empty_state(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.favorites.index'));

        $response->assertOk();
        $response->assertSee('Belum Ada Kos Favorit');
        $response->assertSee('Cari Kos');
    }

    // ── Booking: CTA clarity + review shows Kos + instant booking intact ──

    public function test_available_room_exposes_booking_cta(): void
    {
        $kos = $this->kos();
        $kamar = $this->kamar($kos, 'available');

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $kos));

        $response->assertOk();
        $response->assertSee("/tenant/booking/create?kos_id={$kos->id}&amp;kamar_id={$kamar->id}", false);
    }

    public function test_unavailable_room_does_not_expose_booking_cta(): void
    {
        $kos = $this->kos();
        $occupied = $this->kamar($kos, 'occupied');

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $kos));

        $response->assertOk();
        $response->assertSee('Kamar Tidak Tersedia');
        $response->assertDontSee("/tenant/booking/create?kos_id={$kos->id}&amp;kamar_id={$occupied->id}", false);
    }

    public function test_booking_review_displays_kos_name(): void
    {
        $kos = $this->kos('Kos Review Nama', 'Jl. Review No. 5');
        $kamar = $this->kamar($kos, 'available');

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.create', ['kos_id' => $kos->id, 'kamar_id' => $kamar->id]));

        $response->assertOk();
        $response->assertSee('Kos Review Nama');
    }

    public function test_booking_review_displays_kos_address(): void
    {
        $kos = $this->kos('Kos Review Alamat', 'Jl. Review No. 5');
        $kamar = $this->kamar($kos, 'available');

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.create', ['kos_id' => $kos->id, 'kamar_id' => $kamar->id]));

        $response->assertOk();
        $response->assertSee('Jl. Review No. 5');
    }

    public function test_booking_review_sequence_kos_kamar_periode_biaya(): void
    {
        $kos = $this->kos('Kos Urut', 'Jl. Urut No. 7');
        $kamar = $this->kamar($kos, 'available', 1500000);

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.create', ['kos_id' => $kos->id, 'kamar_id' => $kamar->id]));

        $response->assertOk();
        $response->assertSee('Review Booking');
        $response->assertSee('Kos Urut');
        $response->assertSee('Jl. Urut No. 7');
        $response->assertSee('Biaya sewa');
    }

    public function test_existing_instant_booking_remains_functional(): void
    {
        $kos = $this->kos();
        $kamar = $this->kamar($kos, 'available', 1500000);

        $response = $this->actingAs($this->tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'rental_type' => 'monthly',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->tenant->id,
            'kamar_id' => $kamar->id,
            'price' => 1500000,
            'status' => 'approved',
        ]);
    }

    // ── Empty states ──

    public function test_empty_state_no_search_results(): void
    {
        $this->kos('Kos Melati');

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.index', ['q' => 'inexistent']));

        $response->assertOk();
        $response->assertSee('Kos tidak ditemukan');
        $response->assertSee('Hapus Pencarian');
    }

    public function test_empty_state_no_available_rooms(): void
    {
        $kos = $this->kos('Kos Penuh');
        $this->kamar($kos, 'occupied');

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.create', ['kos_id' => $kos->id]));

        $response->assertOk();
        $response->assertSee('Tidak ada kamar tersedia');
        $response->assertSee('Cari kos lain');
    }

    public function test_empty_state_no_location_results(): void
    {
        $this->kos('Kos A', 'Jl. Ada No. 1');

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.index', ['loc' => 'Jl. Tidak Ada No. 99']));

        $response->assertOk();
        $response->assertSee('Kos tidak ditemukan di lokasi ini');
        $response->assertSee('Hapus Lokasi');
    }

    // ── Query state preservation ──

    public function test_sort_option_in_top_dropdown_matches_drawer(): void
    {
        $this->kos('Kos A');

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.index'));

        $response->assertOk();
        $response->assertSee('terbanyak_disewa');
        $response->assertSee('paling_banyak_favorit');
    }

    public function test_sort_by_most_rented_functional(): void
    {
        $kosA = $this->kos('Kos A');
        $this->kamar($kosA, 'available');
        $kosB = $this->kos('Kos B');
        $this->kamar($kosB, 'available');

        Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $kosA->id,
            'status' => 'approved',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addMonths(1)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.index', ['sort' => 'terbanyak_disewa']));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Kos A', 'Kos B']);
    }

    public function test_query_state_preserved_across_filters(): void
    {
        $wifi = Fasilitas::factory()->create(['name' => 'WiFi']);
        $kos1 = $this->kos('Kos Query', 'Jl. Query No. 1');
        $kamar1 = $this->kamar($kos1, 'available', 800000);
        $kamar1->fasilitas()->attach($wifi->id);
        $this->kos('Kos Lain', 'Jl. Lain No. 2');

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.index', [
            'q' => 'Query',
            'loc' => 'Jl. Query No. 1',
            'price_min' => 500000,
            'price_max' => 1000000,
            'facilities' => [$wifi->id],
            'tersedia_only' => 1,
            'sort' => 'harga_terendah',
        ]));

        $response->assertOk();
        $response->assertSee('Kos Query');
        $response->assertDontSee('Kos Lain');
    }

    public function test_pagination_preserves_query_state(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $kos = $this->kos("Kos Paginate {$i}");
            $this->kamar($kos, 'available');
        }

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.index', ['q' => 'Paginate', 'page' => 2]));

        $response->assertOk();
    }

    public function test_reset_clears_active_filters(): void
    {
        $this->kos('Kos Reset');

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.index', ['q' => 'something', 'tersedia_only' => 1]));

        $response->assertOk();
        $response->assertSee('Reset');
    }

    // ── Navigation ──

    public function test_tenant_navigation_routes_valid(): void
    {
        $routes = [
            route('dashboard'),
            route('tenant.kos.index'),
            route('tenant.favorites.index'),
            route('tenant.booking.index'),
        ];

        foreach ($routes as $route) {
            $this->actingAs($this->tenant)->get($route)->assertOk();
        }
    }

    public function test_mobile_navigation_renders(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.index'));

        $response->assertOk();
        $response->assertSee('Beranda');
        $response->assertSee(route('tenant.favorites.index'));
        $response->assertSee(route('tenant.booking.index'));
    }

    public function test_desktop_sidebar_navigation_renders(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Cari Kos');
        $response->assertSee('Favorit');
        $response->assertSee('Booking Saya');
    }

    // ── Accessibility ──

    public function test_result_count_has_status_role(): void
    {
        $this->kos('Kos Akses');
        $this->kamar($this->kos('Kos Akses 2'), 'available');

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.index'));

        $response->assertOk();
        $response->assertSee('role="status"', false);
    }
}
