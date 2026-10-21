<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KamarCapacityValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Kos $kos;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
    }

    public function test_capacity_can_be_1(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kamar.store'), [
            'kos_id' => $this->kos->id,
            'room_number' => 'S-01',
            'room_name' => 'Single',
            'room_type' => 'Standard',
            'capacity' => 1,
            'daily_price' => 100000,
            'weekly_price' => 300000,
            'monthly_price' => 1000000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kamar', ['room_number' => 'S-01', 'capacity' => 1, 'billing_mode' => 'personal']);
    }

    public function test_capacity_can_be_2(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kamar.store'), [
            'kos_id' => $this->kos->id,
            'room_number' => 'S-02',
            'room_name' => 'Double',
            'room_type' => 'Sharing',
            'capacity' => 2,
            'daily_price' => 100000,
            'weekly_price' => 300000,
            'monthly_price' => 1000000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kamar', ['room_number' => 'S-02', 'capacity' => 2, 'billing_mode' => 'shared']);
    }

    public function test_capacity_can_be_3(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kamar.store'), [
            'kos_id' => $this->kos->id,
            'room_number' => 'S-03',
            'room_name' => 'Triple',
            'room_type' => 'Sharing',
            'capacity' => 3,
            'daily_price' => 100000,
            'weekly_price' => 300000,
            'monthly_price' => 1000000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kamar', ['room_number' => 'S-03', 'capacity' => 3, 'billing_mode' => 'shared']);
    }

    public function test_capacity_can_be_4(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kamar.store'), [
            'kos_id' => $this->kos->id,
            'room_number' => 'S-04',
            'room_name' => 'Family',
            'room_type' => 'Family',
            'capacity' => 4,
            'daily_price' => 100000,
            'weekly_price' => 300000,
            'monthly_price' => 1000000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kamar', ['room_number' => 'S-04', 'capacity' => 4, 'billing_mode' => 'shared']);
    }

    public function test_capacity_zero_is_rejected(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kamar.store'), [
            'kos_id' => $this->kos->id,
            'room_number' => 'S-05',
            'room_name' => 'Invalid',
            'room_type' => 'Standard',
            'capacity' => 0,
            'daily_price' => 100000,
            'weekly_price' => 300000,
            'monthly_price' => 1000000,
        ]);

        $response->assertSessionHasErrors('capacity');
    }

    public function test_capacity_negative_is_rejected(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kamar.store'), [
            'kos_id' => $this->kos->id,
            'room_number' => 'S-06',
            'room_name' => 'Invalid',
            'room_type' => 'Standard',
            'capacity' => -1,
            'daily_price' => 100000,
            'weekly_price' => 300000,
            'monthly_price' => 1000000,
        ]);

        $response->assertSessionHasErrors('capacity');
    }

    public function test_capacity_above_max_is_rejected(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kamar.store'), [
            'kos_id' => $this->kos->id,
            'room_number' => 'S-07',
            'room_name' => 'Invalid',
            'room_type' => 'Standard',
            'capacity' => 21,
            'daily_price' => 100000,
            'weekly_price' => 300000,
            'monthly_price' => 1000000,
        ]);

        $response->assertSessionHasErrors('capacity');
    }

    public function test_booking_with_occupants_exceeding_capacity_is_rejected(): void
    {
        $kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'capacity' => 2,
            'billing_mode' => 'shared',
            'status' => 'available',
            'monthly_price' => 1000000,
            'daily_price' => 50000,
            'weekly_price' => 300000,
        ]);

        $svc = app(BookingService::class);
        $result = $svc->create([
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
            'requested_occupants' => 5,
        ], $this->tenant->id);

        $this->assertFalse($result['ok']);
        $this->assertSame('capacity_exceeded', $result['error']);
    }

    public function test_booking_with_occupants_equal_to_capacity_is_allowed(): void
    {
        $kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'capacity' => 3,
            'billing_mode' => 'shared',
            'status' => 'available',
            'monthly_price' => 1000000,
            'daily_price' => 50000,
            'weekly_price' => 300000,
        ]);

        $svc = app(BookingService::class);
        $result = $svc->create([
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
            'requested_occupants' => 3,
        ], $this->tenant->id);

        $this->assertTrue($result['ok']);
    }

    public function test_personal_booking_with_multiple_occupants_is_rejected(): void
    {
        $kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'capacity' => 3,
            'billing_mode' => 'personal',
            'status' => 'available',
            'monthly_price' => 1000000,
            'daily_price' => 50000,
            'weekly_price' => 300000,
        ]);

        $svc = app(BookingService::class);
        $result = $svc->create([
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
            'requested_occupants' => 2,
        ], $this->tenant->id);

        $this->assertFalse($result['ok']);
        $this->assertSame('personal_multi_rejected', $result['error']);
    }

    public function test_booking_occupant_count_within_capacity_allowed(): void
    {
        $kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'capacity' => 4,
            'billing_mode' => 'shared',
            'status' => 'available',
            'monthly_price' => 1000000,
            'daily_price' => 50000,
            'weekly_price' => 300000,
        ]);

        $svc = app(BookingService::class);
        $result = $svc->create([
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
            'requested_occupants' => 4,
        ], $this->tenant->id);

        $this->assertTrue($result['ok']);
    }

    public function test_owner_can_add_occupants_up_to_capacity(): void
    {
        $kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'capacity' => 2,
            'billing_mode' => 'shared',
            'status' => 'available',
            'monthly_price' => 1000000,
        ]);

        $u1 = User::factory()->create(['role' => 'tenant']);
        $u2 = User::factory()->create(['role' => 'tenant']);
        $u3 = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($this->owner)->post(route('owner.kamar.add-penghuni', $kamar), [
            'user_id' => $u1->id, 'rental_type' => 'monthly', 'rental_price' => 1000000,
            'identity_number' => '123', 'phone' => '0812', 'address' => 'Test', 'check_in_date' => now()->toDateString(),
        ])->assertRedirect();

        $this->actingAs($this->owner)->post(route('owner.kamar.add-penghuni', $kamar), [
            'user_id' => $u2->id, 'rental_type' => 'monthly', 'rental_price' => 1000000,
            'identity_number' => '1234', 'phone' => '0813', 'address' => 'Test', 'check_in_date' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseCount('penghunis', 2);

        $response = $this->actingAs($this->owner)->post(route('owner.kamar.add-penghuni', $kamar), [
            'user_id' => $u3->id, 'rental_type' => 'monthly', 'rental_price' => 1000000,
            'identity_number' => '1235', 'phone' => '0814', 'address' => 'Test', 'check_in_date' => now()->toDateString(),
        ]);
        $response->assertStatus(400);
        $this->assertDatabaseCount('penghunis', 2);
    }
}
