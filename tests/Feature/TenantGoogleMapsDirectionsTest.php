<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantGoogleMapsDirectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function tenant(): User
    {
        return User::factory()->create(['role' => 'tenant']);
    }

    protected function makeKos(array $overrides = []): Kos
    {
        return Kos::factory()->create(array_merge([
            'status' => 'active',
            'latitude' => null,
            'longitude' => null,
            'address' => '',
        ], $overrides));
    }

    // ── Model: URL generation & validation ──────────────────────

    public function test_kos_with_valid_coordinates_generates_directions_url(): void
    {
        $kos = $this->makeKos(['latitude' => -7.7956000, 'longitude' => 110.3695000]);

        $this->assertSame(
            'https://www.google.com/maps/dir/?api=1&destination=-7.7956000,110.3695000',
            $kos->googleMapsDirectionsUrl()
        );
    }

    public function test_directions_url_uses_exact_kos_latitude(): void
    {
        $kos = $this->makeKos(['latitude' => -6.2000000, 'longitude' => 106.8167000]);

        $url = $kos->googleMapsDirectionsUrl();

        $this->assertStringContainsString('destination=-6.2000000,106.8167000', $url);
    }

    public function test_directions_url_uses_exact_kos_longitude(): void
    {
        $kos = $this->makeKos(['latitude' => -6.2000000, 'longitude' => 106.8167000]);

        $this->assertStringContainsString('106.8167000', $kos->googleMapsDirectionsUrl());
    }

    public function test_kos_without_coordinates_falls_back_to_address(): void
    {
        $kos = $this->makeKos(['address' => 'Jl. Sudirman No. 10, Yogyakarta']);

        $this->assertSame(
            'https://www.google.com/maps/dir/?api=1&destination='.urlencode('Jl. Sudirman No. 10, Yogyakarta'),
            $kos->googleMapsDirectionsUrl()
        );
    }

    public function test_address_is_url_encoded(): void
    {
        $kos = $this->makeKos(['address' => 'Jl. A Yani No. 5, Kota & Kab. Bandung']);

        $url = $kos->googleMapsDirectionsUrl();

        $this->assertStringContainsString('destination=', $url);
        $this->assertStringEndsWith(
            'destination='.urlencode('Jl. A Yani No. 5, Kota & Kab. Bandung'),
            $url
        );
    }

    public function test_whitespace_only_address_is_treated_as_missing(): void
    {
        $kos = $this->makeKos(['address' => '   ']);

        $this->assertNull($kos->googleMapsDirectionsUrl());
    }

    public function test_no_coordinates_and_no_address_returns_null_url(): void
    {
        $kos = $this->makeKos();

        $this->assertNull($kos->googleMapsDirectionsUrl());
    }

    public function test_invalid_latitude_falls_back_to_address(): void
    {
        $kos = $this->makeKos(['latitude' => 95, 'longitude' => 110.3695000, 'address' => 'Jl. Kemang No. 3']);

        $this->assertSame(
            'https://www.google.com/maps/dir/?api=1&destination='.urlencode('Jl. Kemang No. 3'),
            $kos->googleMapsDirectionsUrl()
        );
    }

    public function test_invalid_longitude_falls_back_to_address(): void
    {
        $kos = $this->makeKos(['latitude' => -7.7956000, 'longitude' => 200, 'address' => 'Jl. Malioboro']);

        $this->assertSame(
            'https://www.google.com/maps/dir/?api=1&destination='.urlencode('Jl. Malioboro'),
            $kos->googleMapsDirectionsUrl()
        );
    }

    public function test_null_latitude_falls_back_to_address(): void
    {
        $kos = $this->makeKos(['latitude' => null, 'longitude' => 110.3695000, 'address' => 'Jl. Merdeka']);

        $this->assertSame(
            'https://www.google.com/maps/dir/?api=1&destination='.urlencode('Jl. Merdeka'),
            $kos->googleMapsDirectionsUrl()
        );
    }

    public function test_empty_string_coordinates_are_rejected(): void
    {
        $kos = $this->makeKos(['latitude' => '-999.5', 'longitude' => '-999.5', 'address' => 'Jl. Veteran No. 1']);

        $this->assertSame(
            'https://www.google.com/maps/dir/?api=1&destination='.urlencode('Jl. Veteran No. 1'),
            $kos->googleMapsDirectionsUrl()
        );
    }

    public function test_non_numeric_coordinates_fall_back_to_address(): void
    {
        $kos = $this->makeKos(['latitude' => '95.5', 'longitude' => '200.5', 'address' => 'Jl. Anggrek']);

        $this->assertSame(
            'https://www.google.com/maps/dir/?api=1&destination='.urlencode('Jl. Anggrek'),
            $kos->googleMapsDirectionsUrl()
        );
    }

    public function test_invalid_coordinates_and_no_address_returns_null_url(): void
    {
        $kos = $this->makeKos(['latitude' => -999, 'longitude' => 999]);

        $this->assertNull($kos->googleMapsDirectionsUrl());
        $this->assertNull($kos->googleMapsSearchUrl());
    }

    public function test_navigation_urls_never_contain_broken_destinations(): void
    {
        $kos = $this->makeKos(['latitude' => '999.5', 'longitude' => '999.5', 'address' => '']);
        $this->assertNull($kos->googleMapsDirectionsUrl());
        $this->assertNull($kos->googleMapsSearchUrl());
    }

    // ── Multi-kos: each kos keeps its OWN destination ───────────

    public function test_different_kos_generate_different_destinations(): void
    {
        $kosA = $this->makeKos(['name' => 'Kos A', 'latitude' => -6.2000000, 'longitude' => 106.8167000]);
        $kosB = $this->makeKos(['name' => 'Kos B', 'latitude' => -7.7956000, 'longitude' => 110.3695000]);

        $this->assertSame(
            'https://www.google.com/maps/dir/?api=1&destination=-6.2000000,106.8167000',
            $kosA->googleMapsDirectionsUrl()
        );
        $this->assertSame(
            'https://www.google.com/maps/dir/?api=1&destination=-7.7956000,110.3695000',
            $kosB->googleMapsDirectionsUrl()
        );
        $this->assertNotSame($kosA->googleMapsDirectionsUrl(), $kosB->googleMapsDirectionsUrl());
    }

    public function test_each_kos_detail_page_uses_its_own_destination(): void
    {
        $tenant = $this->tenant();
        $kosA = $this->makeKos(['name' => 'Kos A', 'latitude' => -6.2000000, 'longitude' => 106.8167000]);
        $kosB = $this->makeKos(['name' => 'Kos B', 'latitude' => -7.7956000, 'longitude' => 110.3695000]);

        $responseA = $this->actingAs($tenant)->get("/tenant/kos/{$kosA->id}")->assertOk();
        $responseB = $this->actingAs($tenant)->get("/tenant/kos/{$kosB->id}")->assertOk();

        $responseA->assertSee('/maps/dir/?api=1&amp;destination=-6.2000000,106.8167000', false);
        $responseA->assertDontSee('/maps/dir/?api=1&amp;destination=-7.7956000,110.3695000', false);

        $responseB->assertSee('/maps/dir/?api=1&amp;destination=-7.7956000,110.3695000', false);
        $responseB->assertDontSee('/maps/dir/?api=1&amp;destination=-6.2000000,106.8167000', false);
    }

    // ── Detail & cards UI ─────────────────────────────────────────

    public function test_detail_page_shows_arah_ke_kos_for_coordinates(): void
    {
        $tenant = $this->tenant();
        $kos = $this->makeKos(['name' => 'Kos Mawar', 'latitude' => -6.2000000, 'longitude' => 106.8167000]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Buka petunjuk arah menuju Kos Mawar di Google Maps', false);
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_detail_page_address_only_shows_arah_ke_kos(): void
    {
        $tenant = $this->tenant();
        $kos = $this->makeKos(['name' => 'Kos Melati', 'address' => 'Jl. Thamrin No. 25, Jakarta']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Arah ke Kos');
        $response->assertSee(urlencode('Jl. Thamrin No. 25, Jakarta'), false);
    }

    public function test_detail_page_no_location_has_no_directions_cta(): void
    {
        $tenant = $this->tenant();
        $kos = $this->makeKos(['name' => 'Kos Tanpa Lokasi']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Lokasi peta belum tersedia.');
        $response->assertDontSee('/maps/dir/?api=1', false);
        $response->assertDontSee('Arah ke Kos');
    }

    public function test_listing_card_address_only_shows_arah_ke_kos(): void
    {
        $tenant = $this->tenant();
        $this->makeKos(['name' => 'Kos Bougenville', 'address' => 'Jl. Gajah Mada No. 12, Semarang']);

        $response = $this->actingAs($tenant)->get('/tenant/kos');

        $response->assertOk();
        $response->assertSee('Arah ke Kos');
        $response->assertSee('maps/dir/?api=1', false);
        $response->assertSee(urlencode('Jl. Gajah Mada No. 12, Semarang'), false);
    }

    public function test_listing_card_no_location_has_no_directions_cta(): void
    {
        $tenant = $this->tenant();
        $this->makeKos(['name' => 'Kos Tanpa Lokasi']);

        $response = $this->actingAs($tenant)->get('/tenant/kos');

        $response->assertOk();
        $response->assertDontSee('Arah ke Kos');
        $response->assertDontSee('/maps/dir/?api=1', false);
    }

    // ── Booking experience: directions for the booked kos ────────

    public function test_booking_review_shows_directions_for_selected_kos(): void
    {
        $tenant = $this->tenant();
        $kos = $this->makeKos(['name' => 'Kos Booking A', 'address' => 'Jl. Bintaro No. 8, Jakarta']);
        Kamar::factory()->create(['kos_id' => $kos->id]);

        $response = $this->actingAs($tenant)->get(route('tenant.booking.create', ['kos_id' => $kos->id]));

        $response->assertOk();
        $response->assertSee('Arah ke Kos');
        $response->assertSee(urlencode('Jl. Bintaro No. 8, Jakarta'), false);
    }

    public function test_booking_review_skips_directions_when_no_location(): void
    {
        $tenant = $this->tenant();
        $kos = $this->makeKos(['name' => 'Kos Tanpa Lokasi']);
        Kamar::factory()->create(['kos_id' => $kos->id]);

        $response = $this->actingAs($tenant)->get(route('tenant.booking.create', ['kos_id' => $kos->id]));

        $response->assertOk();
        $response->assertDontSee('Arah ke Kos');
    }

    // ── Existing features remain intact ───────────────────────────

    public function test_existing_detail_functionality_remains_intact(): void
    {
        $tenant = $this->tenant();
        $kos = $this->makeKos([
            'name' => 'Kos Lengkap',
            'address' => 'Jl. Kaliurang No. 7',
            'latitude' => -7.7700000,
            'longitude' => 110.4000000,
        ]);
        Kamar::factory()->create(['kos_id' => $kos->id]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Kos Lengkap');
        $response->assertSee('Lokasi Kos');
        $response->assertSee('Buka di Google Maps');
        $response->assertSee('Petunjuk Arah');
    }

    public function test_existing_marketplace_functionality_remains_intact(): void
    {
        $tenant = $this->tenant();
        $this->makeKos(['name' => 'Kos Market', 'address' => 'Jl. Demangan No. 2', 'latitude' => -7.7800000, 'longitude' => 110.3800000]);

        $response = $this->actingAs($tenant)->get('/tenant/kos');

        $response->assertOk();
        $response->assertSee('Kos Market');
        $response->assertSee('Lihat Detail');
    }

    public function test_existing_booking_functionality_remains_intact(): void
    {
        $tenant = $this->tenant();
        $kos = $this->makeKos(['name' => 'Kos Booking Regresi', 'address' => 'Jl. Sagan No. 4', 'latitude' => -7.7700000, 'longitude' => 110.3700000]);
        Kamar::factory()->create(['kos_id' => $kos->id]);

        $response = $this->actingAs($tenant)->get(route('tenant.booking.create', ['kos_id' => $kos->id]));

        $response->assertOk();
        $response->assertSee('Kos Booking Regresi');
    }
}
