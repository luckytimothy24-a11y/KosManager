<?php

namespace Tests\Feature;

use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantGoogleMapsTest extends TestCase
{
    use RefreshDatabase;

    protected function tenant(): User
    {
        return User::factory()->create(['role' => 'tenant']);
    }

    // ── Kos WITH coordinates ───────────────────────────────────

    public function test_detail_with_coordinates_shows_google_maps_section(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => -7.7956000,
            'longitude' => 110.3695000,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Lokasi Kos');
        $response->assertSee('Buka di Google Maps');
    }

    public function test_google_maps_url_uses_correct_coordinates(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => -7.7956000,
            'longitude' => 110.3695000,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('maps/search/?api=1', false);
        $response->assertSee(urlencode('-7.7956000,110.3695000'), false);
    }

    public function test_directions_url_uses_correct_coordinates(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => -6.2000000,
            'longitude' => 106.8167000,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('maps/dir/?api=1', false);
        $response->assertSee(urlencode('-6.2000000,106.8167000'), false);
    }

    public function test_map_preview_iframe_shows_for_kos_with_coordinates(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => -7.7956000,
            'longitude' => 110.3695000,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('maps.google.com/maps?q=', false);
        $response->assertSee('output=embed', false);
    }

    public function test_map_preview_is_clickable_link_to_google_maps(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => -7.7956000,
            'longitude' => 110.3695000,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('maps/search/?api=1', false);
        $response->assertSee(urlencode('-7.7956000,110.3695000'), false);
        // Kotak peta terbungkus link yang membuka Google Maps di tab baru.
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_map_preview_has_accessible_name_and_hint(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'name' => 'Kos Melati Jaya',
            'latitude' => -7.7956000,
            'longitude' => 110.3695000,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Lihat lokasi Kos Melati Jaya di Google Maps', false);
        $response->assertSee('Klik untuk membuka Maps', false);
    }

    public function test_map_preview_hint_not_shown_without_coordinates(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => null,
            'longitude' => null,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertDontSee('Klik untuk membuka Maps');
    }

    public function test_gmaps_links_open_in_new_tab(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => -7.7956000,
            'longitude' => 110.3695000,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_accessible_name_includes_kos_name(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'name' => 'Kos Melati Jaya',
            'latitude' => -7.7956000,
            'longitude' => 110.3695000,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Buka lokasi Kos Melati Jaya di Google Maps', false);
        $response->assertSee('Buka petunjuk arah menuju Kos Melati Jaya di Google Maps', false);
    }

    public function test_address_always_shows_as_text(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'address' => 'Jl. Sudirman No. 10, Yogyakarta',
            'latitude' => -7.7956000,
            'longitude' => 110.3695000,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Jl. Sudirman No. 10, Yogyakarta');
    }

    // ── Kos WITHOUT coordinates (null) ────────────────────────

    public function test_detail_without_coordinates_shows_fallback(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => null,
            'longitude' => null,
            'address' => '',
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Lokasi peta belum tersedia.');
    }

    public function test_detail_without_coordinates_but_with_address_shows_directions_fallback(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'address' => 'Jl. Melati No. 9, Yogyakarta',
            'latitude' => null,
            'longitude' => null,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Arah ke Kos');
        $response->assertSee(urlencode('Jl. Melati No. 9, Yogyakarta'), false);
        $response->assertDontSee('Buka di Google Maps');
    }

    public function test_detail_without_coordinates_does_not_show_gmaps_link(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => null,
            'longitude' => null,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertDontSee('google.com/maps/search', false);
        $response->assertDontSee('Buka di Google Maps');
    }

    public function test_detail_without_coordinates_does_not_show_iframe(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => null,
            'longitude' => null,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertDontSee('maps.google.com/maps?q=', false);
        $response->assertDontSee('output=embed', false);
    }

    public function test_detail_without_coordinates_does_not_generate_empty_url(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => null,
            'longitude' => null,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        // Should not contain Google Maps URL with empty/missing coordinates
        $response->assertDontSee('google.com/maps/search/?api=1&query=,', false);
        $response->assertDontSee('google.com/maps/search/?api=1&query=null', false);
    }

    public function test_detail_with_null_lat_shows_fallback(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => null,
            'longitude' => 110.3695000,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        // Koordinat tak lengkap + alamat ada → fallback petunjuk arah via alamat.
        $response->assertSee('Arah ke Kos');
        $response->assertDontSee('google.com/maps/search', false);
    }

    public function test_detail_with_null_long_shows_fallback(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => -7.7956000,
            'longitude' => null,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Arah ke Kos');
        $response->assertDontSee('google.com/maps/search', false);
    }

    public function test_address_shows_even_without_coordinates(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'address' => 'Jl. Merdeka No. 5, Bandung',
            'latitude' => null,
            'longitude' => null,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Jl. Merdeka No. 5, Bandung');
    }

    public function test_llocation_section_always_present(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create(['status' => 'active']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Lokasi Kos');
    }

    // ── Authorization ─────────────────────────────────────────

    public function test_unauthenticated_user_cannot_view_kos_detail(): void
    {
        $kos = Kos::factory()->create(['status' => 'active', 'latitude' => -7.7956000, 'longitude' => 110.3695000]);

        $this->get("/tenant/kos/{$kos->id}")->assertRedirect('/login');
    }

    public function test_owner_cannot_access_tenant_kos_detail(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['status' => 'active', 'latitude' => -7.7956000, 'longitude' => 110.3695000]);

        $this->actingAs($owner)->get("/tenant/kos/{$kos->id}")->assertForbidden();
    }

    public function test_inactive_kos_not_visible(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'inactive',
            'latitude' => -7.7956000,
            'longitude' => 110.3695000,
        ]);

        $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}")->assertNotFound();
    }

    // ── Listing page (index) ──────────────────────────────────

    public function test_listing_card_shows_maps_link_with_coordinates(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => -7.7956000,
            'longitude' => 110.3695000,
        ]);

        $response = $this->actingAs($tenant)->get('/tenant/kos');

        $response->assertOk();
        $response->assertSee('Arah ke Kos');
        $response->assertSee('maps/dir/?api=1', false);
        $response->assertSee('-7.7956000,110.3695000', false);
    }

    public function test_listing_card_hides_directions_link_without_location(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => null,
            'longitude' => null,
            'address' => '',
        ]);

        $response = $this->actingAs($tenant)->get('/tenant/kos');

        $response->assertOk();
        $response->assertDontSee('Arah ke Kos');
    }

    // ── Existing features regression (no disruption) ──────────

    public function test_search_still_works_with_google_maps_section(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'name' => 'Kos Melati',
            'status' => 'active',
            'latitude' => -7.7956000,
            'longitude' => 110.3695000,
        ]);

        $response = $this->actingAs($tenant)->get('/tenant/kos?q=Melati');

        $response->assertOk();
        $response->assertSee('Kos Melati');
    }

    public function test_filter_still_works_with_google_maps_section(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'name' => 'Kos Filter Test',
            'status' => 'active',
            'latitude' => -7.7956000,
            'longitude' => 110.3695000,
        ]);

        $response = $this->actingAs($tenant)->get('/tenant/kos?tersedia_only=1');

        $response->assertOk();
    }

    public function test_sort_still_works_with_google_maps_section(): void
    {
        $tenant = $this->tenant();

        $response = $this->actingAs($tenant)->get('/tenant/kos?sort=harga_terendah');

        $response->assertOk();
    }

    public function test_favorite_still_works_with_google_maps_section(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => -7.7956000,
            'longitude' => 110.3695000,
        ]);

        $response = $this->actingAs($tenant)->post(route('tenant.favorites.toggle'), ['kos_id' => $kos->id]);

        $response->assertRedirect();
    }

    public function test_detail_page_still_shows_kos_info_alongside_google_maps(): void
    {
        $tenant = $this->tenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'name' => 'Kos Komplit',
            'address' => 'Jl. Test No. 1',
            'latitude' => -7.7956000,
            'longitude' => 110.3695000,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Kos Komplit');
        $response->assertSee('Jl. Test No. 1');
        $response->assertSee('Lokasi Kos');
        $response->assertSee('Buka di Google Maps');
        $response->assertSee('Petunjuk Arah');
    }
}
