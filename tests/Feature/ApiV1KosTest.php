<?php

namespace Tests\Feature;

use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Kos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1KosTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------- index

    public function test_kos_index_returns_locked_contract_shape(): void
    {
        $fasilitas = Fasilitas::factory()->create(['name' => 'WiFi', 'is_active' => true]);
        $kos = Kos::factory()->create([
            'status' => 'active',
            'name' => 'Kos Harmoni',
            'address' => 'Jl. Contoh No. 10',
            'description' => 'Kos nyaman dekat kampus.',
            'photo' => 'kos/kos-harmoni.jpg',
        ]);
        $kos->fasilitas()->attach($fasilitas);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'monthly_price' => 850000]);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'monthly_price' => 1200000]);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'booked', 'monthly_price' => 1500000]);

        $response = $this->getJson('/api/v1/kos');

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'name',
                    'slug',
                    'address',
                    'city',
                    'description',
                    'price_min',
                    'price_max',
                    'photo',
                    'facilities',
                    'available_rooms',
                    'is_available',
                ]],
                'meta' => ['current_page', 'per_page', 'last_page', 'total'],
            ]);

        $item = $response->json('data.0');
        $this->assertSame($kos->id, $item['id']);
        $this->assertSame('Kos Harmoni', $item['name']);
        $this->assertSame('Jl. Contoh No. 10', $item['address']);
        $this->assertSame(850000, (int) $item['price_min']);
        $this->assertSame(1200000, (int) $item['price_max']);
        $this->assertSame(['WiFi'], $item['facilities']);
        $this->assertSame(2, $item['available_rooms']);
        $this->assertTrue($item['is_available']);
        $this->assertNull($item['slug']);
        $this->assertNull($item['city']);
    }

    public function test_kos_index_defaults_per_page_to_15(): void
    {
        Kos::factory()->count(20)->create(['status' => 'active']);

        $response = $this->getJson('/api/v1/kos');

        $response->assertOk();
        $this->assertSame(15, $response->json('meta.per_page'));
        $this->assertSame(2, $response->json('meta.last_page'));
        $this->assertSame(20, $response->json('meta.total'));
        $this->assertCount(15, $response->json('data'));
    }

    public function test_kos_index_caps_per_page_at_50(): void
    {
        Kos::factory()->count(5)->create(['status' => 'active']);

        $response = $this->getJson('/api/v1/kos?per_page=200');

        $response->assertOk();
        $this->assertSame(50, $response->json('meta.per_page'));
    }

    public function test_kos_index_returns_empty_collection_with_200(): void
    {
        $response = $this->getJson('/api/v1/kos');

        $response->assertOk()
            ->assertJson([
                'data' => [],
                'meta' => ['current_page' => 1, 'per_page' => 15, 'last_page' => 1, 'total' => 0],
            ]);
    }

    public function test_kos_index_only_lists_active_kos(): void
    {
        Kos::factory()->create(['status' => 'active']);
        Kos::factory()->create(['status' => 'inactive']);

        $response = $this->getJson('/api/v1/kos');

        $this->assertSame(1, $response->json('meta.total'));
        $this->assertCount(1, $response->json('data'));
    }

    public function test_kos_index_does_not_expose_internal_fields(): void
    {
        Kos::factory()->create(['status' => 'active']);

        $response = $this->getJson('/api/v1/kos');
        $item = $response->json('data.0');

        $this->assertArrayNotHasKey('owner_id', $item);
        $this->assertArrayNotHasKey('latitude', $item);
        $this->assertArrayNotHasKey('longitude', $item);
        $this->assertArrayNotHasKey('phone', $item);
        $this->assertArrayNotHasKey('general_facilities', $item);
        $this->assertArrayNotHasKey('rules', $item);
        $this->assertArrayNotHasKey('payment_info', $item);
        $this->assertArrayNotHasKey('status', $item);
        $this->assertArrayNotHasKey('created_at', $item);
        $this->assertArrayNotHasKey('updated_at', $item);
        $this->assertArrayNotHasKey('deleted_at', $item);
        $this->assertArrayNotHasKey('owner', $item);
    }

    // ---------------------------------------------------------------- show

    public function test_kos_show_returns_locked_contract_shape(): void
    {
        $fasilitas = Fasilitas::factory()->create(['name' => 'WiFi', 'is_active' => true]);
        $kos = Kos::factory()->create([
            'status' => 'active',
            'photo' => 'kos/kos-harmoni.jpg',
        ]);
        $kos->fasilitas()->attach($fasilitas);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'monthly_price' => 850000]);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'monthly_price' => 1200000]);

        $response = $this->getJson("/api/v1/kos/{$kos->id}");

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure(['data' => [
                'id',
                'name',
                'slug',
                'address',
                'city',
                'description',
                'price_min',
                'price_max',
                'photos',
                'facilities',
                'available_rooms',
                'is_available',
            ]]);

        $data = $response->json('data');
        $this->assertSame([$kos->photo], $data['photos']);
        $this->assertSame(850000, (int) $data['price_min']);
        $this->assertSame(1200000, (int) $data['price_max']);
        $this->assertSame(['WiFi'], $data['facilities']);
        $this->assertSame(2, $data['available_rooms']);
        $this->assertTrue($data['is_available']);
    }

    public function test_kos_show_wraps_null_photo_as_empty_photos(): void
    {
        $kos = Kos::factory()->create(['status' => 'active', 'photo' => null]);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $response = $this->getJson("/api/v1/kos/{$kos->id}");

        $response->assertOk()
            ->assertJson(['data' => ['photos' => []]]);
    }

    public function test_kos_show_returns_404_for_inactive_kos(): void
    {
        $kos = Kos::factory()->create(['status' => 'inactive']);

        $response = $this->getJson("/api/v1/kos/{$kos->id}");

        $response->assertStatus(404)
            ->assertJson(['message' => 'Resource not found.']);
    }

    public function test_kos_show_returns_404_for_missing_kos(): void
    {
        $response = $this->getJson('/api/v1/kos/999999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Resource not found.']);
    }

    public function test_kos_show_does_not_expose_owner_payment_or_internal_fields(): void
    {
        $kos = Kos::factory()->create(['status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $data = $this->getJson("/api/v1/kos/{$kos->id}")->json('data');

        $this->assertArrayNotHasKey('owner', $data);
        $this->assertArrayNotHasKey('owner_id', $data);
        $this->assertArrayNotHasKey('tenant', $data);
        $this->assertArrayNotHasKey('payment', $data);
        $this->assertArrayNotHasKey('payment_info', $data);
        $this->assertArrayNotHasKey('rules', $data);
        $this->assertArrayNotHasKey('status', $data);
        $this->assertArrayNotHasKey('created_at', $data);
    }

    // ---------------------------------------------------------------- kamar

    public function test_kos_kamar_returns_locked_contract_shape(): void
    {
        $kos = Kos::factory()->create(['status' => 'active']);
        Kamar::factory()->create([
            'kos_id' => $kos->id,
            'room_name' => 'Kamar A01',
            'room_type' => 'Standard',
            'monthly_price' => 850000,
            'photo' => 'kamar/a01.jpg',
            'status' => 'available',
        ]);
        Kamar::factory()->create([
            'kos_id' => $kos->id,
            'room_name' => 'Kamar A02',
            'room_type' => 'Standard',
            'monthly_price' => 850000,
            'photo' => null,
            'status' => 'booked',
        ]);

        $response = $this->getJson("/api/v1/kos/{$kos->id}/kamar");

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'name',
                    'type',
                    'price',
                    'photo',
                    'status',
                    'is_available',
                ]],
                'meta' => ['current_page', 'per_page', 'last_page', 'total'],
            ]);

        $data = $response->json('data');
        $this->assertSame(2, $response->json('meta.total'));

        $available = collect($data)->firstWhere('status', 'available');
        $booked = collect($data)->firstWhere('status', 'booked');

        $this->assertSame('Kamar A01', $available['name']);
        $this->assertSame('Standard', $available['type']);
        $this->assertSame(850000, (int) $available['price']);
        $this->assertSame('kamar/a01.jpg', $available['photo']);
        $this->assertTrue($available['is_available']);
        $this->assertFalse($booked['is_available']);
    }

    public function test_kos_kamar_returns_404_for_inactive_kos(): void
    {
        $kos = Kos::factory()->create(['status' => 'inactive']);

        $this->getJson("/api/v1/kos/{$kos->id}/kamar")
            ->assertStatus(404)
            ->assertJson(['message' => 'Resource not found.']);
    }

    public function test_kos_kamar_returns_empty_collection_with_200(): void
    {
        $kos = Kos::factory()->create(['status' => 'active']);

        $response = $this->getJson("/api/v1/kos/{$kos->id}/kamar");

        $response->assertOk()
            ->assertJson([
                'data' => [],
                'meta' => ['current_page' => 1, 'per_page' => 15, 'last_page' => 1, 'total' => 0],
            ]);
    }

    public function test_kos_kamar_does_not_expose_tenant_or_booking_details(): void
    {
        $kos = Kos::factory()->create(['status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'booked']);

        $item = $this->getJson("/api/v1/kos/{$kos->id}/kamar")->json('data.0');

        $this->assertArrayNotHasKey('kos_id', $item);
        $this->assertArrayNotHasKey('room_number', $item);
        $this->assertArrayNotHasKey('floor', $item);
        $this->assertArrayNotHasKey('daily_price', $item);
        $this->assertArrayNotHasKey('area', $item);
        $this->assertArrayNotHasKey('description', $item);
        $this->assertArrayNotHasKey('tenant', $item);
        $this->assertArrayNotHasKey('penghuni', $item);
        $this->assertArrayNotHasKey('phone', $item);
        $this->assertArrayNotHasKey('kontrak', $item);
        $this->assertArrayNotHasKey('payment', $item);
        $this->assertArrayNotHasKey('booking', $item);
        $this->assertArrayNotHasKey('created_at', $item);
    }

    // ---------------------------------------------------------------- misc contract

    public function test_kos_endpoints_return_empty_facilities_when_relation_absent(): void
    {
        $kos = Kos::factory()->create(['status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $this->getJson('/api/v1/kos')->assertJson(['data' => [['facilities' => []]]]);
        $this->getJson("/api/v1/kos/{$kos->id}")->assertJson(['data' => ['facilities' => []]]);
    }

    public function test_kos_availability_reflects_existing_domain_logic(): void
    {
        $available = Kos::factory()->create(['status' => 'active']);
        Kamar::factory()->create(['kos_id' => $available->id, 'status' => 'available']);

        $noRooms = Kos::factory()->create(['status' => 'active']);

        $response = $this->getJson('/api/v1/kos');

        $byId = collect($response->json('data'))->keyBy('id');
        $this->assertTrue($byId[$available->id]['is_available']);
        $this->assertFalse($byId[$noRooms->id]['is_available']);
        $this->assertSame(0, $byId[$noRooms->id]['available_rooms']);
    }
}
