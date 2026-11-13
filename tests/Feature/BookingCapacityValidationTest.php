<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCapacityValidationTest extends TestCase
{
    use RefreshDatabase;

    private function bookingPayload(Kos $kos, Kamar $kamar, array $overrides = []): array
    {
        return array_merge([
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'rental_type' => 'monthly',
            'requested_occupants' => 1,
            'identity_number' => '12345678',
            'phone' => '081234567890',
            'address' => 'Test Address',
        ], $overrides);
    }

    public function test_booking_request_rejects_multiple_occupants(): void
    {
        $tenant = User::factory()->create(['role' => 'pencari']);
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'capacity' => 1, 'billing_mode' => 'personal', 'status' => 'available']);

        $response = $this->actingAs($tenant)->postJson('/api/v1/bookings', $this->bookingPayload($kos, $kamar, [
            'requested_occupants' => 3,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('requested_occupants');
    }

    public function test_booking_request_allows_single_occupant(): void
    {
        $tenant = User::factory()->create(['role' => 'pencari']);
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'capacity' => 1, 'billing_mode' => 'personal', 'status' => 'available']);

        $response = $this->actingAs($tenant)->postJson('/api/v1/bookings', $this->bookingPayload($kos, $kamar));

        $response->assertStatus(201);
    }

    public function test_booking_request_normalizes_zero_occupants_to_one(): void
    {
        $tenant = User::factory()->create(['role' => 'pencari']);
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->postJson('/api/v1/bookings', $this->bookingPayload($kos, $kamar, [
            'requested_occupants' => 0,
        ]));

        $response->assertStatus(201);
        $this->assertDatabaseHas('bookings', [
            'user_id' => $tenant->id,
            'kamar_id' => $kamar->id,
            'requested_occupants' => 1,
        ]);
    }

    public function test_booking_request_rejects_shared_billing_mode(): void
    {
        $tenant = User::factory()->create(['role' => 'pencari']);
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'capacity' => 1, 'billing_mode' => 'personal', 'status' => 'available']);

        $response = $this->actingAs($tenant)->postJson('/api/v1/bookings', $this->bookingPayload($kos, $kamar, [
            'billing_mode' => 'shared',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('billing_mode');
    }
}
