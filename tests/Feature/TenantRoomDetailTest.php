<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantRoomDetailTest extends TestCase
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

    // ── Room Facilities ────────────────────────────────────────

    public function test_tenant_can_view_room_facilities_on_kos_detail(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $ac = Fasilitas::create(['name' => 'AC']);
        $wifi = Fasilitas::create(['name' => 'WiFi']);
        $kamar->fasilitas()->attach([$ac->id, $wifi->id]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('AC');
        $response->assertSee('WiFi');
    }

    public function test_room_facilities_come_from_database_not_hardcoded(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $uniqueFacility = Fasilitas::create(['name' => 'UniqueFacilityPhase42']);
        $kamar->fasilitas()->attach($uniqueFacility->id);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('UniqueFacilityPhase42');
    }

    public function test_room_without_facilities_shows_no_facility_section(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Kamar '.$kamar->room_number);
    }

    public function test_facility_icon_is_displayed_when_present(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $f = Fasilitas::create(['name' => 'WiFi', 'icon' => 'ri-wifi-line']);
        $kamar->fasilitas()->attach($f->id);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('ri-wifi-line');
        $response->assertSee('WiFi');
    }

    public function test_facility_without_icon_displays_name_only(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $f = Fasilitas::create(['name' => 'Lemari', 'icon' => null]);
        $kamar->fasilitas()->attach($f->id);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Lemari');
    }

    // ── Room Detail ────────────────────────────────────────────

    public function test_all_rooms_shown_not_only_available(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamarAvail = Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => 'A01', 'status' => 'available']);
        $kamarOccupied = Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => 'A02', 'status' => 'occupied']);
        $kamarBooked = Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => 'A03', 'status' => 'booked']);
        $kamarMaint = Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => 'A04', 'status' => 'maintenance']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('A01');
        $response->assertSee('A02');
        $response->assertSee('A03');
        $response->assertSee('A04');
        $response->assertSee('4 kamar total');
    }

    public function test_room_status_labels_are_correct(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => 'X01', 'status' => 'available']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => 'X02', 'status' => 'occupied']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => 'X03', 'status' => 'booked']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => 'X04', 'status' => 'maintenance']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('TERSEDIA');
        $response->assertSee('TERISI');
        $response->assertSee('DI-BOOKING');
        $response->assertSee('MAINTENANCE');
    }

    public function test_room_price_is_displayed(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create([
            'kos_id' => $kos->id,
            'daily_price' => 100000,
            'monthly_price' => 2500000,
            'status' => 'available',
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('100.000');
        $response->assertSee('2.500.000');
    }

    public function test_room_description_shown_in_detail_modal(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create([
            'kos_id' => $kos->id,
            'description' => 'Kamar strategis dekat kampus',
            'status' => 'available',
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Kamar strategis dekat kampus');
    }

    public function test_unavailable_room_shows_disabled_booking(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => 'OCC1', 'status' => 'occupied']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Kamar Tidak Tersedia');
    }

    // ── Availability & Booking ─────────────────────────────────

    public function test_available_room_has_booking_link(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Booking', escape: false);
        $response->assertSee("/tenant/booking/create?kos_id={$kos->id}&amp;kamar_id={$kamar->id}", escape: false);
    }

    public function test_booking_preserves_kamar_id(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get("/tenant/booking/create?kos_id={$kos->id}&kamar_id={$kamar->id}");

        $response->assertOk();
        $response->assertSee((string) $kamar->id);
    }

    public function test_booking_server_side_price_calculation(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create([
            'kos_id' => $kos->id,
            'monthly_price' => 1500000,
            'status' => 'available',
        ]);

        $response = $this->actingAs($tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'rental_type' => 'monthly',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'user_id' => $tenant->id,
            'kamar_id' => $kamar->id,
            'price' => 1500000,
            'status' => 'approved',
        ]);
    }

    public function test_cannot_book_unavailable_room(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'occupied']);

        $response = $this->actingAs($tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
        ]);

        $response->assertSessionHasErrors('kamar_id');
    }

    // ── Google Maps / Location ─────────────────────────────────

    public function test_kos_with_coordinates_shows_map(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => -7.7955798,
            'longitude' => 110.4052787,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('maps.google.com', escape: false);
        $response->assertSee('Buka di Google Maps');
        $response->assertSee('Lokasi');
    }

    public function test_kos_without_coordinates_shows_fallback(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => null,
            'longitude' => null,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Lokasi peta belum ditentukan oleh pengelola');
        $response->assertDontSee('maps.google.com', escape: false);
    }

    public function test_google_maps_url_uses_correct_coordinates(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('google.com/maps/search/?api=1', escape: false);
        $response->assertSee('maps.google.com', escape: false);
    }

    public function test_kos_with_null_latitude_does_not_generate_fake_marker(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'latitude' => null,
            'longitude' => null,
        ]);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertDontSee('maps.google.com', escape: false);
        $response->assertDontSee('api=1');
    }

    // ── Location Authorization ─────────────────────────────────

    public function test_owner_can_update_own_kos_location(): void
    {
        $owner = $this->createOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($owner)->put(route('owner.kos.update', $kos), [
            'name' => $kos->name,
            'address' => $kos->address,
            'phone' => $kos->phone,
            'latitude' => -7.7955798,
            'longitude' => 110.4052787,
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kos', [
            'id' => $kos->id,
            'latitude' => -7.7955798,
            'longitude' => 110.4052787,
        ]);
    }

    public function test_owner_cannot_update_other_owner_kos_location(): void
    {
        $ownerA = $this->createOwner();
        $ownerB = $this->createOwner();
        $kosB = Kos::factory()->create(['owner_id' => $ownerB->id]);

        $response = $this->actingAs($ownerA)->put(route('owner.kos.update', $kosB), [
            'name' => 'Hacked',
            'address' => 'Jl. Hack',
            'phone' => '081111111111',
            'latitude' => -7.79,
            'longitude' => 110.40,
            'status' => 'active',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('kos', ['id' => $kosB->id, 'latitude' => null]);
    }

    public function test_tenant_cannot_modify_kos_location(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);

        $response = $this->actingAs($tenant)->put(route('owner.kos.update', $kos), [
            'name' => $kos->name,
            'address' => $kos->address,
            'phone' => $kos->phone,
            'latitude' => -7.79,
            'longitude' => 110.40,
            'status' => 'active',
        ]);

        $response->assertForbidden();
    }

    public function test_location_validation_rejects_invalid_coordinates(): void
    {
        $owner = $this->createOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($owner)->put(route('owner.kos.update', $kos), [
            'name' => $kos->name,
            'address' => $kos->address,
            'phone' => $kos->phone,
            'latitude' => 999,
            'longitude' => 999,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors(['latitude', 'longitude']);
    }

    // ── Facility Filter Consistency ────────────────────────────

    public function test_facility_filter_matches_room_facilities(): void
    {
        $tenant = $this->createTenant();
        $ac = Fasilitas::create(['name' => 'AC']);

        $kosWithAC = Kos::factory()->create(['name' => 'Kos AC', 'status' => 'active']);
        $kamarAC = Kamar::factory()->create(['kos_id' => $kosWithAC->id, 'status' => 'available']);
        $kamarAC->fasilitas()->attach($ac->id);

        $kosNoAC = Kos::factory()->create(['name' => 'Kos No AC', 'status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kosNoAC->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->get("/tenant/kos?facilities[]={$ac->id}");

        $response->assertOk();
        $response->assertSee('Kos AC');
        $response->assertDontSee('Kos No AC');

        $detailResponse = $this->actingAs($tenant)->get("/tenant/kos/{$kosWithAC->id}");
        $detailResponse->assertOk();
        $detailResponse->assertSee('AC');
    }

    // ── Performance (N+1) ─────────────────────────────────────

    public function test_no_n_plus_one_queries_for_room_facilities(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'active']);

        for ($i = 0; $i < 5; $i++) {
            $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
            $f = Fasilitas::create(['name' => "Facility {$i}"]);
            $kamar->fasilitas()->attach($f->id);
        }

        $baseCount = DB::getQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertOk();

        $kamarQueries = collect($queries)->filter(fn ($q) => str_contains($q['query'], 'kamar_fasilitas') ||
            str_contains($q['query'], 'fasilitas')
        )->count();

        $this->assertLessThanOrEqual(3, $kamarQueries, 'Expected eager loading for fasilitas, not N+1');
    }

    // ── General Facilities vs Room Facilities ──────────────────

    public function test_general_facilities_separate_from_room_facilities(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create([
            'status' => 'active',
            'general_facilities' => 'Parkir, CCTV, Dapur Bersama',
        ]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $ac = Fasilitas::create(['name' => 'AC']);
        $kamar->fasilitas()->attach($ac->id);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('Fasilitas Kos');
        $response->assertSee('Parkir');
        $response->assertSee('CCTV');
        $response->assertSee('AC');
    }

    // ── Tenant Isolation ──────────────────────────────────────

    public function test_tenant_isolation_remains_intact(): void
    {
        $tenantA = $this->createTenant();
        $tenantB = $this->createTenant();
        $owner = $this->createOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $booking = Booking::factory()->create([
            'user_id' => $tenantA->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
        ]);

        $responseB = $this->actingAs($tenantB)->get("/tenant/kos/{$kos->id}");
        $responseB->assertOk();
        $responseB->assertDontSee($booking->booking_code);

        $responseB = $this->actingAs($tenantB)->get(route('tenant.booking.show', $booking));
        $responseB->assertForbidden();
    }

    // ── Guest Access ──────────────────────────────────────────

    public function test_guest_cannot_view_kos_detail(): void
    {
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $f = Fasilitas::create(['name' => 'WiFi']);
        $kamar->fasilitas()->attach($f->id);

        $response = $this->get("/tenant/kos/{$kos->id}");

        $response->assertRedirect('/login');
    }

    public function test_inactive_kos_returns_404(): void
    {
        $tenant = $this->createTenant();
        $kos = Kos::factory()->create(['status' => 'inactive']);

        $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}")->assertNotFound();
    }

    // ── Owner Location on Create ───────────────────────────────

    public function test_owner_can_create_kos_with_location(): void
    {
        $owner = $this->createOwner();

        $response = $this->actingAs($owner)->post(route('owner.kos.store'), [
            'name' => 'Kos Baru',
            'address' => 'Jl. Baru No. 1',
            'phone' => '081234567890',
            'latitude' => -7.7955798,
            'longitude' => 110.4052787,
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kos', [
            'name' => 'Kos Baru',
            'latitude' => -7.7955798,
            'longitude' => 110.4052787,
        ]);
    }

    public function test_owner_can_create_kos_without_location(): void
    {
        $owner = $this->createOwner();

        $response = $this->actingAs($owner)->post(route('owner.kos.store'), [
            'name' => 'Kos Tanpa Lokasi',
            'address' => 'Jl. Tanpa No. 1',
            'phone' => '081234567890',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kos', [
            'name' => 'Kos Tanpa Lokasi',
            'latitude' => null,
            'longitude' => null,
        ]);
    }

    // ── Existing Booking Flow Not Broken ───────────────────────

    public function test_existing_booking_flow_still_works(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'notes' => 'Tennis',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'user_id' => $tenant->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('kamar', [
            'id' => $kamar->id,
            'status' => 'booked',
        ]);
    }

    public function test_booking_cancel_still_works(): void
    {
        $tenant = $this->createTenant();
        $owner = $this->createOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'booked']);

        $booking = Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($tenant)->post(route('tenant.booking.cancel', $booking));

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'available']);
    }
}
