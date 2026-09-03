<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiV1BookingTest extends TestCase
{
    use RefreshDatabase;

    private User $tenant;

    private User $owner;

    private Kos $kos;

    private Kamar $kamar;

    private function token(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('secret123')]);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'available',
            'monthly_price' => 1000000,
            'daily_price' => 100000,
        ]);
    }

    private function makeBooking(array $overrides = []): Booking
    {
        return Booking::factory()->create(array_merge([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addMonths(2),
            'rental_type' => 'monthly',
            'status' => 'approved',
        ], $overrides));
    }

    // ---------------------------------------------------------------- index

    public function test_index_returns_locked_contract_shape(): void
    {
        $this->makeBooking();

        $response = $this->withToken($this->token($this->tenant))->getJson('/api/v1/bookings');

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'booking_code',
                    'kos' => ['id', 'name', 'slug', 'address', 'city', 'photo'],
                    'kamar' => ['id', 'name', 'type', 'photo'],
                    'booking_date',
                    'start_date',
                    'end_date',
                    'rental_type',
                    'price',
                    'status',
                    'notes',
                ]],
                'meta' => ['current_page', 'per_page', 'last_page', 'total'],
            ]);

        $item = $response->json('data.0');
        $this->assertSame($this->kamar->id, $item['kamar']['id']);
        $this->assertSame($this->kos->id, $item['kos']['id']);
        $this->assertSame('approved', $item['status']);
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/bookings')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_index_requires_tenant_role(): void
    {
        $this->withToken($this->token($this->owner))
            ->getJson('/api/v1/bookings')
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_index_returns_only_own_bookings(): void
    {
        $other = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('secret123')]);
        $own = $this->makeBooking();
        $otherBooking = $this->makeBooking(['user_id' => $other->id]);

        $response = $this->withToken($this->token($this->tenant))->getJson('/api/v1/bookings');

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.total'));
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($own->id, $ids);
        $this->assertNotContains($otherBooking->id, $ids);
    }

    public function test_index_returns_empty_collection_with_200(): void
    {
        $response = $this->withToken($this->token($this->tenant))->getJson('/api/v1/bookings');

        $response->assertOk()
            ->assertJson([
                'data' => [],
                'meta' => ['current_page' => 1, 'per_page' => 15, 'last_page' => 1, 'total' => 0],
            ]);
    }

    public function test_index_defaults_per_page_to_15_and_caps_at_50(): void
    {
        $this->makeBooking();

        $default = $this->withToken($this->token($this->tenant))->getJson('/api/v1/bookings');
        $this->assertSame(15, $default->json('meta.per_page'));

        $capped = $this->withToken($this->token($this->tenant))->getJson('/api/v1/bookings?per_page=200');
        $this->assertSame(50, $capped->json('meta.per_page'));
    }

    public function test_index_does_not_expose_internal_or_sensitive_fields(): void
    {
        $this->makeBooking(['notes' => 'privatsz']);

        $item = $this->withToken($this->token($this->tenant))->getJson('/api/v1/bookings')->json('data.0');

        $this->assertArrayNotHasKey('user_id', $item);
        $this->assertArrayNotHasKey('kos_id', $item);
        $this->assertArrayNotHasKey('kamar_id', $item);
        $this->assertArrayNotHasKey('created_at', $item);
        $this->assertArrayNotHasKey('updated_at', $item);
        $this->assertArrayNotHasKey('deleted_at', $item);
        $this->assertArrayNotHasKey('user', $item);
        $this->assertArrayNotHasKey('payment', $item);
        $this->assertArrayNotHasKey('owner', $item);
    }

    // ---------------------------------------------------------------- show

    public function test_show_returns_booking_detail(): void
    {
        $booking = $this->makeBooking();

        $response = $this->withToken($this->token($this->tenant))->getJson("/api/v1/bookings/{$booking->id}");

        $response->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'booking_code',
                'kos',
                'kamar',
                'booking_date',
                'start_date',
                'end_date',
                'rental_type',
                'price',
                'status',
                'notes',
            ]]);
        $this->assertSame($booking->booking_code, $response->json('data.booking_code'));
    }

    public function test_show_denies_other_tenant_booking(): void
    {
        $other = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('secret123')]);
        $booking = $this->makeBooking(['user_id' => $other->id]);

        $this->withToken($this->token($this->tenant))->getJson("/api/v1/bookings/{$booking->id}")
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_show_requires_authentication(): void
    {
        $booking = $this->makeBooking();

        $this->getJson("/api/v1/bookings/{$booking->id}")
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_show_returns_404_for_missing_booking(): void
    {
        $this->withToken($this->token($this->tenant))->getJson('/api/v1/bookings/999999')
            ->assertStatus(404)
            ->assertJson(['message' => 'Resource not found.']);
    }

    // ---------------------------------------------------------------- store

    public function test_store_creates_booking_and_returns_201(): void
    {
        $response = $this->withToken($this->token($this->tenant))->postJson('/api/v1/bookings', [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'notes' => 'Mau kosong',
        ]);

        $response->assertStatus(201)
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure(['data' => ['id', 'booking_code', 'status'], 'message']);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->tenant->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'approved',
            'price' => 1000000,
        ]);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'booked']);
    }

    public function test_store_uses_existing_rental_price(): void
    {
        $this->kamar->update(['daily_price' => 150000]);

        $response = $this->withToken($this->token($this->tenant))->postJson('/api/v1/bookings', [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'rental_type' => 'daily',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('bookings', ['kamar_id' => $this->kamar->id, 'rental_type' => 'daily', 'price' => 150000]);
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/v1/bookings', [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'rental_type' => 'daily',
        ])->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_store_requires_tenant_role(): void
    {
        $this->withToken($this->token($this->owner))->postJson('/api/v1/bookings', [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'rental_type' => 'daily',
        ])->assertStatus(403)->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->withToken($this->token($this->tenant))->postJson('/api/v1/bookings', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['kos_id', 'kamar_id', 'start_date', 'end_date', 'rental_type']]);
    }

    public function test_store_validates_rental_type_enum(): void
    {
        $response = $this->withToken($this->token($this->tenant))->postJson('/api/v1/bookings', [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'rental_type' => 'weekly',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['rental_type']]);
    }

    public function test_store_rejects_invalid_dates(): void
    {
        $response = $this->withToken($this->token($this->tenant))->postJson('/api/v1/bookings', [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'rental_type' => 'monthly',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['end_date']]);
    }

    public function test_store_rejects_invalid_kos_id(): void
    {
        $response = $this->withToken($this->token($this->tenant))->postJson('/api/v1/bookings', [
            'kos_id' => 999999,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'rental_type' => 'daily',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['kos_id']]);
    }

    public function test_store_rejects_occupied_room(): void
    {
        $this->kamar->update(['status' => 'occupied']);

        $response = $this->withToken($this->token($this->tenant))->postJson('/api/v1/bookings', [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'rental_type' => 'daily',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['kamar_id']]);
    }

    public function test_store_rejects_overlapping_approved_booking(): void
    {
        $this->makeBooking([
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(10),
            'status' => 'approved',
        ]);

        $response = $this->withToken($this->token($this->tenant))->postJson('/api/v1/bookings', [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(2)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'rental_type' => 'monthly',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['kamar_id']]);
    }

    // ---------------------------------------------------------------- cancel

    public function test_cancel_own_booking(): void
    {
        $booking = $this->makeBooking(['status' => 'approved']);

        $response = $this->withToken($this->token($this->tenant))->postJson("/api/v1/bookings/{$booking->id}/cancel");

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJson(['message' => 'Booking berhasil dibatalkan.']);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'available']);
    }

    public function test_cancel_denies_other_tenant_booking(): void
    {
        $other = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('secret123')]);
        $booking = $this->makeBooking(['user_id' => $other->id, 'status' => 'approved']);

        $this->withToken($this->token($this->tenant))->postJson("/api/v1/bookings/{$booking->id}/cancel")
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'approved']);
    }

    public function test_cancel_requires_tenant_role(): void
    {
        $booking = $this->makeBooking(['status' => 'approved']);

        $this->withToken($this->token($this->owner))->postJson("/api/v1/bookings/{$booking->id}/cancel")
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_cancel_requires_authentication(): void
    {
        $booking = $this->makeBooking(['status' => 'approved']);

        $this->postJson("/api/v1/bookings/{$booking->id}/cancel")
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_cannot_cancel_terminal_booking(): void
    {
        $booking = $this->makeBooking(['status' => 'cancelled']);

        $this->withToken($this->token($this->tenant))->postJson("/api/v1/bookings/{$booking->id}/cancel")
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'cancelled']);
    }

    public function test_cannot_cancel_completed_booking(): void
    {
        $booking = $this->makeBooking(['status' => 'completed']);

        $this->withToken($this->token($this->tenant))->postJson("/api/v1/bookings/{$booking->id}/cancel")
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'completed']);
    }
}
