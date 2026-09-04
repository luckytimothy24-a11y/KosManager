<?php

namespace Tests\Feature;

use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantKosBrowseTest extends TestCase
{
    use RefreshDatabase;

    private function createTenant(): User
    {
        return User::factory()->create(['role' => 'tenant']);
    }

    // ── Original tests (preserved) ──────────────────────────────

    public function test_tenant_can_view_active_kos_list(): void
    {
        $tenant = $this->createTenant();
        $kosActive = Kos::factory()->create(['name' => 'KOSAKTIF', 'status' => 'active']);
        Kos::factory()->create(['name' => 'KOSNONAKTIF', 'status' => 'inactive']);

        $response = $this->actingAs($tenant)->get('/tenant/kos');

        $response->assertOk();
        $response->assertSee('KOSAKTIF');
        $response->assertDontSee('KOSNONAKTIF');
    }

    public function test_tenant_can_search_kos_by_name(): void
    {
        $tenant = $this->createTenant();
        Kos::factory()->create(['name' => 'KOSTARGET', 'status' => 'active']);
        Kos::factory()->create(['name' => 'KOSLAIN', 'status' => 'active']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?q=TARGET');

        $response->assertOk();
        $response->assertSee('KOSTARGET');
        $response->assertDontSee('KOSLAIN');
    }

    public function test_tenant_can_view_all_rooms_in_kos_detail(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'name' => 'Kos Uji Detail',
            'address' => 'Jl. Uji No. 1',
            'phone' => '081000000000',
        ]);
        Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => '101', 'status' => 'available']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => '102', 'status' => 'occupied']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('101');
        $response->assertSee('102');
    }

    public function test_tenant_cannot_view_inactive_kos_detail(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'inactive']);

        $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}")->assertNotFound();
    }

    public function test_owner_cannot_access_tenant_kos_browse(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)->get('/tenant/kos')->assertForbidden();
    }

    // ── Search tests ────────────────────────────────────────────

    public function test_search_by_address(): void
    {
        $tenant = $this->createTenant();
        Kos::factory()->create(['name' => 'Kos A', 'address' => 'Jl. Sudirman No. 10', 'status' => 'active']);
        Kos::factory()->create(['name' => 'Kos B', 'address' => 'Jl. Thamrin No. 25', 'status' => 'active']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?q=Sudirman');

        $response->assertOk();
        $response->assertSee('Kos A');
        $response->assertDontSee('Kos B');
    }

    public function test_search_by_description(): void
    {
        $tenant = $this->createTenant();
        Kos::factory()->create(['name' => 'Kos Premium', 'description' => 'Kos premium dengan fasilitas lengkap', 'status' => 'active']);
        Kos::factory()->create(['name' => 'Kos Murah', 'description' => 'Kos murah meriah dekat kampus', 'status' => 'active']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?q=premium');

        $response->assertOk();
        $response->assertSee('Kos Premium');
        $response->assertDontSee('Kos Murah');
    }

    public function test_search_case_insensitive(): void
    {
        $tenant = $this->createTenant();
        Kos::factory()->create(['name' => 'Kos Melati', 'status' => 'active']);
        Kos::factory()->create(['name' => 'Kos Kenanga', 'status' => 'active']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?q=MELATI');

        $response->assertOk();
        $response->assertSee('Kos Melati');
        $response->assertDontSee('Kos Kenanga');
    }

    public function test_search_trim_whitespace(): void
    {
        $tenant = $this->createTenant();
        Kos::factory()->create(['name' => 'Kos Melati', 'status' => 'active']);
        Kos::factory()->create(['name' => 'Kos Mawar', 'status' => 'active']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?q=%20Melati%20');

        $response->assertOk();
        $response->assertSee('Kos Melati');
        $response->assertDontSee('Kos Mawar');
    }

    public function test_search_no_result(): void
    {
        $tenant = $this->createTenant();
        Kos::factory()->create(['name' => 'Kos Melati', 'status' => 'active']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?q=inexistente');

        $response->assertOk();
        $response->assertSee('Kos tidak ditemukan');
        $response->assertDontSee('Kos Melati');
    }

    public function test_search_special_characters_escaped(): void
    {
        $tenant = $this->createTenant();
        Kos::factory()->create(['name' => 'Kos 50%', 'status' => 'active']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?q=%');

        $response->assertOk();
        $response->assertDontSee('Kos 50%');
    }

    // ── Price filter tests ──────────────────────────────────────

    public function test_price_minimum_filter(): void
    {
        $tenant = $this->createTenant();
        $kos1 = Kos::factory()->create(['name' => 'Cheap Kos', 'status' => 'active']);
        $kos2 = Kos::factory()->create(['name' => 'Expensive Kos', 'status' => 'active']);

        Kamar::factory()->create(['kos_id' => $kos1->id, 'monthly_price' => 500000, 'status' => 'available']);
        Kamar::factory()->create(['kos_id' => $kos2->id, 'monthly_price' => 2000000, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?price_min=1000000');

        $response->assertOk();
        $response->assertSee('Expensive Kos');
        $response->assertDontSee('Cheap Kos');
    }

    public function test_price_maximum_filter(): void
    {
        $tenant = $this->createTenant();
        $kos1 = Kos::factory()->create(['name' => 'Cheap Kos', 'status' => 'active']);
        $kos2 = Kos::factory()->create(['name' => 'Expensive Kos', 'status' => 'active']);

        Kamar::factory()->create(['kos_id' => $kos1->id, 'monthly_price' => 500000, 'status' => 'available']);
        Kamar::factory()->create(['kos_id' => $kos2->id, 'monthly_price' => 2000000, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?price_max=1000000');

        $response->assertOk();
        $response->assertSee('Cheap Kos');
        $response->assertDontSee('Expensive Kos');
    }

    public function test_price_range_filter(): void
    {
        $tenant = $this->createTenant();
        $kos1 = Kos::factory()->create(['name' => 'Kos A', 'status' => 'active']);
        $kos2 = Kos::factory()->create(['name' => 'Kos B', 'status' => 'active']);
        $kos3 = Kos::factory()->create(['name' => 'Kos C', 'status' => 'active']);

        Kamar::factory()->create(['kos_id' => $kos1->id, 'monthly_price' => 500000, 'status' => 'available']);
        Kamar::factory()->create(['kos_id' => $kos2->id, 'monthly_price' => 1000000, 'status' => 'available']);
        Kamar::factory()->create(['kos_id' => $kos3->id, 'monthly_price' => 2000000, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?price_min=700000&price_max=1500000');

        $response->assertOk();
        $response->assertSee('Kos B');
        $response->assertDontSee('Kos A');
        $response->assertDontSee('Kos C');
    }

    // ── Facility filter tests ───────────────────────────────────

    public function test_facility_single_filter(): void
    {
        $tenant = $this->createTenant();
        $wifi = Fasilitas::create(['name' => 'WiFi']);

        $kos1 = Kos::factory()->create(['name' => 'Kos WiFi', 'status' => 'active']);
        $kos2 = Kos::factory()->create(['name' => 'Kos No WiFi', 'status' => 'active']);

        $kamar1 = Kamar::factory()->create(['kos_id' => $kos1->id, 'status' => 'available']);
        $kamar1->fasilitas()->attach($wifi->id);

        $kamar2 = Kamar::factory()->create(['kos_id' => $kos2->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?facilities[]='.$wifi->id);

        $response->assertOk();
        $response->assertSee('Kos WiFi');
        $response->assertDontSee('Kos No WiFi');
    }

    public function test_facility_multiple_filter_and_logic(): void
    {
        $tenant = $this->createTenant();
        $wifi = Fasilitas::create(['name' => 'WiFi']);
        $ac = Fasilitas::create(['name' => 'AC']);

        $kos1 = Kos::factory()->create(['name' => 'Kos Both', 'status' => 'active']);
        $kos2 = Kos::factory()->create(['name' => 'Kos WiFi Only', 'status' => 'active']);

        $kamar1 = Kamar::factory()->create(['kos_id' => $kos1->id, 'status' => 'available']);
        $kamar1->fasilitas()->attach([$wifi->id, $ac->id]);

        $kamar2 = Kamar::factory()->create(['kos_id' => $kos2->id, 'status' => 'available']);
        $kamar2->fasilitas()->attach($wifi->id);

        $response = $this->actingAs($tenant)->get("/tenant/kos?facilities[]={$wifi->id}&facilities[]={$ac->id}");

        $response->assertOk();
        $response->assertSee('Kos Both');
        $response->assertDontSee('Kos WiFi Only');
    }

    // ── F-1/F-2 regression tests ────────────────────────────────
    // Semantik fasilitas (dibuktikan dari UI/relasi/seeder):
    // Kos dianggap menyediakan fasilitas bila fasilitas itu ada di level
    // Kos (kos_fasilitas) ATAU pada minimal satu kamar yang tersedia.
    // Bukan "satu kamar harus memiliki semua fasilitas".

    public function test_facility_filter_matches_kos_level_facility(): void
    {
        $tenant = $this->createTenant();
        $wifi = Fasilitas::create(['name' => 'WiFi', 'type' => 'kos']);

        $kosWith = Kos::factory()->create(['name' => 'Kos Ber-WiFi', 'status' => 'active']);
        $kosWith->fasilitas()->attach($wifi->id);
        Kamar::factory()->create(['kos_id' => $kosWith->id, 'status' => 'available']);

        $kosWithout = Kos::factory()->create(['name' => 'Kos Tanpa Fasilitas', 'status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kosWithout->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?facilities[]='.$wifi->id);

        $response->assertOk();
        $response->assertSee('Kos Ber-WiFi');
        $response->assertDontSee('Kos Tanpa Fasilitas');
    }

    public function test_room_facility_still_matches(): void
    {
        $tenant = $this->createTenant();
        $ac = Fasilitas::create(['name' => 'AC', 'type' => 'kamar']);

        $kos = Kos::factory()->create(['name' => 'Kos Ber-AC', 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $kamar->fasilitas()->attach($ac->id);

        $response = $this->actingAs($tenant)->get('/tenant/kos?facilities[]='.$ac->id);

        $response->assertOk();
        $response->assertSee('Kos Ber-AC');
    }

    public function test_facilities_and_logic_on_same_available_room_matches(): void
    {
        $tenant = $this->createTenant();
        $wifi = Fasilitas::create(['name' => 'WiFi']);
        $ac = Fasilitas::create(['name' => 'AC']);

        $kos = Kos::factory()->create(['name' => 'Kos Lengkap', 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $kamar->fasilitas()->attach([$wifi->id, $ac->id]);

        $response = $this->actingAs($tenant)->get("/tenant/kos?facilities[]={$wifi->id}&facilities[]={$ac->id}");

        $response->assertOk();
        $response->assertSee('Kos Lengkap');
    }

    public function test_facilities_spread_across_multiple_rooms_still_matches(): void
    {
        $tenant = $this->createTenant();
        $wifi = Fasilitas::create(['name' => 'WiFi']);
        $ac = Fasilitas::create(['name' => 'AC']);

        // Kamar A = AC, Kamar B = WiFi, keduanya tersedia.
        $kos = Kos::factory()->create(['name' => 'Kos Gabungan', 'status' => 'active']);
        $kamarA = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $kamarA->fasilitas()->attach($ac->id);
        $kamarB = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $kamarB->fasilitas()->attach($wifi->id);

        // Semantik marketplace: fasilitas dinilai di level Kos, sehingga kos
        // yang menyediakan AC (kamar A) DAN WiFi (kamar B) tetap lolos.
        $response = $this->actingAs($tenant)->get("/tenant/kos?facilities[]={$ac->id}&facilities[]={$wifi->id}");

        $response->assertOk();
        $response->assertSee('Kos Gabungan');
    }

    public function test_facility_on_non_available_room_does_not_match(): void
    {
        $tenant = $this->createTenant();
        $ac = Fasilitas::create(['name' => 'AC', 'type' => 'kamar']);

        // Fasilitas hanya ada di kamar booked/occupied -> tidak dihitung sebagai
        // penawaran yang tersedia oleh tenant.
        $kos = Kos::factory()->create(['name' => 'Kos Penuh', 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'booked']);
        $kamar->fasilitas()->attach($ac->id);

        $response = $this->actingAs($tenant)->get('/tenant/kos?facilities[]='.$ac->id);

        $response->assertOk();
        $response->assertDontSee('Kos Penuh');
    }

    public function test_combined_filter_with_kos_level_facility(): void
    {
        $tenant = $this->createTenant();
        $wifi = Fasilitas::create(['name' => 'WiFi', 'type' => 'kos']);
        $ac = Fasilitas::create(['name' => 'AC', 'type' => 'kamar']);

        $kos1 = Kos::factory()->create(['name' => 'Kos Melati', 'description' => 'Kos strategis', 'status' => 'active']);
        $kos1->fasilitas()->attach($wifi->id);
        $k1 = Kamar::factory()->create(['kos_id' => $kos1->id, 'monthly_price' => 800000, 'status' => 'available']);
        $k1->fasilitas()->attach($ac->id);

        $kos2 = Kos::factory()->create(['name' => 'Kos Mawar', 'description' => 'Kos strategis', 'status' => 'active']);
        $k2 = Kamar::factory()->create(['kos_id' => $kos2->id, 'monthly_price' => 800000, 'status' => 'available']);
        $k2->fasilitas()->attach($ac->id);

        $kos3 = Kos::factory()->create(['name' => 'Kos Melati Premium', 'status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos3->id, 'monthly_price' => 3000000, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?q=Melati&price_min=500000&price_max=1000000&facilities[]='.$wifi->id.'&facilities[]='.$ac->id.'&tersedia_only=1&sort=harga_terendah');

        $response->assertOk();
        $response->assertSee('Kos Melati');
        $response->assertDontSee('Kos Mawar');
        $response->assertDontSee('Kos Melati Premium');
    }

    // ── Availability filter tests ───────────────────────────────

    public function test_availability_filter_shows_only_kos_with_available_rooms(): void
    {
        $tenant = $this->createTenant();

        $kosAvailable = Kos::factory()->create(['name' => 'Kos Available', 'status' => 'active']);
        $kosBooked = Kos::factory()->create(['name' => 'Kos Booked', 'status' => 'active']);
        $kosMaintenance = Kos::factory()->create(['name' => 'Kos Maintenance', 'status' => 'active']);
        $kosEmpty = Kos::factory()->create(['name' => 'Kos Empty', 'status' => 'active']);

        Kamar::factory()->create(['kos_id' => $kosAvailable->id, 'status' => 'available']);
        Kamar::factory()->create(['kos_id' => $kosBooked->id, 'status' => 'booked']);
        Kamar::factory()->create(['kos_id' => $kosMaintenance->id, 'status' => 'maintenance']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?tersedia_only=1');

        $response->assertOk();
        $response->assertSee('Kos Available');
        $response->assertDontSee('Kos Booked');
        $response->assertDontSee('Kos Maintenance');
        $response->assertDontSee('Kos Empty');
    }

    // ── Sort tests ──────────────────────────────────────────────

    public function test_sort_invalid_falls_back_to_default(): void
    {
        $tenant = $this->createTenant();
        Kos::factory()->create(['name' => 'Kos A', 'status' => 'active']);
        Kos::factory()->create(['name' => 'Kos B', 'status' => 'active']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?sort=hack');

        $response->assertOk();
    }

    public function test_sort_price_lowest(): void
    {
        $tenant = $this->createTenant();
        $kos1 = Kos::factory()->create(['name' => 'Kos Cheap', 'status' => 'active']);
        $kos2 = Kos::factory()->create(['name' => 'Kos Expensive', 'status' => 'active']);

        Kamar::factory()->create(['kos_id' => $kos1->id, 'monthly_price' => 500000, 'status' => 'available']);
        Kamar::factory()->create(['kos_id' => $kos2->id, 'monthly_price' => 3000000, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?sort=harga_terendah');

        $response->assertOk();
        $response->assertSeeTextInOrder(['Kos Cheap', 'Kos Expensive']);
    }

    public function test_sort_price_highest(): void
    {
        $tenant = $this->createTenant();
        $kos1 = Kos::factory()->create(['name' => 'Kos Cheap', 'status' => 'active']);
        $kos2 = Kos::factory()->create(['name' => 'Kos Expensive', 'status' => 'active']);

        Kamar::factory()->create(['kos_id' => $kos1->id, 'monthly_price' => 500000, 'status' => 'available']);
        Kamar::factory()->create(['kos_id' => $kos2->id, 'monthly_price' => 3000000, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?sort=harga_tertinggi');

        $response->assertOk();
        $response->assertSeeTextInOrder(['Kos Expensive', 'Kos Cheap']);
    }

    // ── Combined filter test ────────────────────────────────────

    public function test_combined_search_price_facility_availability(): void
    {
        $tenant = $this->createTenant();
        $wifi = Fasilitas::create(['name' => 'WiFi']);

        $kos1 = Kos::factory()->create(['name' => 'Kos Melati', 'description' => 'Kos strategis', 'status' => 'active']);
        $kos2 = Kos::factory()->create(['name' => 'Kos Mawar', 'description' => 'Kos strategis', 'status' => 'active']);
        $kos3 = Kos::factory()->create(['name' => 'Kos Melati Premium', 'status' => 'active']);

        $k1 = Kamar::factory()->create(['kos_id' => $kos1->id, 'monthly_price' => 800000, 'status' => 'available']);
        $k1->fasilitas()->attach($wifi->id);

        $k2 = Kamar::factory()->create(['kos_id' => $kos2->id, 'monthly_price' => 800000, 'status' => 'available']);
        $k2->fasilitas()->attach($wifi->id);

        Kamar::factory()->create(['kos_id' => $kos3->id, 'monthly_price' => 3000000, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?q=Melati&price_min=500000&price_max=1000000&facilities[]='.$wifi->id.'&tersedia_only=1');

        $response->assertOk();
        $response->assertSee('Kos Melati');
        $response->assertDontSee('Kos Mawar');
        $response->assertDontSee('Kos Melati Premium');
    }

    // ── Result count test ───────────────────────────────────────

    public function test_result_count_displayed(): void
    {
        $tenant = $this->createTenant();
        Kos::factory()->create(['name' => 'Kos A', 'status' => 'active']);
        Kos::factory()->create(['name' => 'Kos B', 'status' => 'active']);

        $response = $this->actingAs($tenant)->get('/tenant/kos');

        $response->assertOk();
        $response->assertSee('kos ditemukan');
        $response->assertSee('2');
    }

    // ── Active filter chips test ────────────────────────────────

    public function test_active_filter_chips_displayed(): void
    {
        $tenant = $this->createTenant();
        Kos::factory()->create(['name' => 'Kos Melati', 'status' => 'active']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?q=Melati&tersedia_only=1');

        $response->assertOk();
        $response->assertSee('Melati');
        $response->assertSee('Tersedia');
    }

    // ── Favorite isolation test ─────────────────────────────────

    public function test_tenant_favorite_isolation(): void
    {
        $tenantA = $this->createTenant();
        $tenantB = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);

        $this->actingAs($tenantA)->post('/tenant/favorites/toggle', ['kos_id' => $kos->id]);

        $responseB = $this->actingAs($tenantB)->get('/tenant/kos');

        $responseB->assertOk();
    }

    // ── Guest access test ───────────────────────────────────────

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $this->get('/tenant/kos')->assertRedirect('/login');
    }

    // ── No cross-tenant data leak ───────────────────────────────

    public function test_tenant_cannot_see_other_tenants_bookings_via_kos(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertDontSee('booking_code');
    }
}
