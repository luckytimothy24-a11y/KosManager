<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCapacityValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_request_rejects_occupants_exceeding_capacity(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'capacity' => 2, 'billing_mode' => 'shared', 'status' => 'available']);
        Penghuni::factory()->create(['kamar_id' => $kamar->id, 'status' => 'active']);

        $response = $this->actingAs($tenant)->postJson('/api/v1/bookings', [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'rental_type' => 'monthly',
            'requested_occupants' => 3,
            'billing_mode' => 'shared',
            'identity_number' => '12345678',
            'phone' => '081234567890',
            'address' => 'Test Address',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('requested_occupants');
    }

    public function test_booking_request_allows_occupants_within_capacity(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'capacity' => 4, 'billing_mode' => 'shared', 'status' => 'available']);

        $response = $this->actingAs($tenant)->postJson('/api/v1/bookings', [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'rental_type' => 'monthly',
            'requested_occupants' => 3,
            'billing_mode' => 'shared',
            'identity_number' => '12345678',
            'phone' => '081234567890',
            'address' => 'Test Address',
        ]);

        $response->assertStatus(201);
    }

    public function test_booking_request_rejects_zero_occupants(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $response = $this->actingAs($tenant)->postJson('/api/v1/bookings', [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'rental_type' => 'monthly',
            'requested_occupants' => 0,
            'identity_number' => '12345678',
            'phone' => '081234567890',
            'address' => 'Test Address',
        ]);

        $response->assertStatus(422);
    }
}