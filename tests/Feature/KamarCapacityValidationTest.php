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
        $this->tenant = User::factory()->create(['role' => 'pencari']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
    }

    private function kamarPayload(array $overrides = []): array
    {
        return array_merge([
            'kos_id' => $this->kos->id,
            'room_number' => 'S-01',
            'room_name' => 'Single',
            'room_type' => 'Standard',
            'capacity' => 1,
            'daily_price' => 100000,
            'weekly_price' => 300000,
            'monthly_price' => 1000000,
        ], $overrides);
    }

    public function test_capacity_can_be_1(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kamar.store'), $this->kamarPayload());

        $response->assertRedirect();
        $this->assertDatabaseHas('kamar', ['room_number' => 'S-01', 'capacity' => 1, 'billing_mode' => 'personal']);
    }

    public function test_capacity_greater_than_one_is_rejected(): void
    {
        foreach ([2, 3, 4] as $capacity) {
            $response = $this->actingAs($this->owner)->post(route('owner.kamar.store'), $this->kamarPayload([
                'room_number' => 'S-0'.$capacity,
                'room_name' => 'Multi '.$capacity,
                'room_type' => 'Sharing',
                'capacity' => $capacity,
            ]));

            $response->assertSessionHasErrors('capacity');
            $this->assertDatabaseMissing('kamar', ['room_number' => 'S-0'.$capacity]);
        }
    }

    public function test_capacity_zero_is_rejected(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kamar.store'), $this->kamarPayload([
            'room_number' => 'S-05',
            'room_name' => 'Invalid',
            'capacity' => 0,
        ]));

        $response->assertSessionHasErrors('capacity');
    }

    public function test_capacity_negative_is_rejected(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kamar.store'), $this->kamarPayload([
            'room_number' => 'S-06',
            'room_name' => 'Invalid',
            'capacity' => -1,
        ]));

        $response->assertSessionHasErrors('capacity');
    }

    public function test_capacity_above_max_is_rejected(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kamar.store'), $this->kamarPayload([
            'room_number' => 'S-07',
            'room_name' => 'Invalid',
            'capacity' => 21,
        ]));

        $response->assertSessionHasErrors('capacity');
    }

    public function test_booking_with_multiple_occupants_is_rejected(): void
    {
        $kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'capacity' => 1,
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

    public function test_booking_with_single_occupant_is_allowed(): void
    {
        $kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'capacity' => 1,
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
            'requested_occupants' => 1,
        ], $this->tenant->id);

        $this->assertTrue($result['ok']);
    }

    public function test_shared_billing_mode_is_rejected_by_service(): void
    {
        $kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'capacity' => 1,
            'billing_mode' => 'personal',
            'status' => 'available',
            'monthly_price' => 1000000,
        ]);

        $svc = app(BookingService::class);
        $result = $svc->create([
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
            'requested_occupants' => 1,
        ], $this->tenant->id);

        $this->assertFalse($result['ok']);
        $this->assertSame('billing_mode_not_allowed', $result['error']);
    }

    public function test_personal_booking_with_multiple_occupants_is_rejected(): void
    {
        $kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'capacity' => 1,
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
            'requested_occupants' => 3,
        ], $this->tenant->id);

        $this->assertFalse($result['ok']);
        $this->assertSame('personal_multi_rejected', $result['error']);
    }

    public function test_owner_can_add_only_one_occupant(): void
    {
        $kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'capacity' => 1,
            'billing_mode' => 'personal',
            'status' => 'available',
            'monthly_price' => 1000000,
        ]);

        $u1 = User::factory()->create(['role' => 'pencari']);
        $u2 = User::factory()->create(['role' => 'pencari']);

        $this->actingAs($this->owner)->post(route('owner.kamar.add-penghuni', $kamar), [
            'user_id' => $u1->id, 'rental_type' => 'monthly', 'rental_price' => 1000000,
            'identity_number' => '123', 'phone' => '0812', 'address' => 'Test', 'check_in_date' => now()->toDateString(),
        ])->assertRedirect();

        $response = $this->actingAs($this->owner)->post(route('owner.kamar.add-penghuni', $kamar), [
            'user_id' => $u2->id, 'rental_type' => 'monthly', 'rental_price' => 1000000,
            'identity_number' => '1234', 'phone' => '0813', 'address' => 'Test', 'check_in_date' => now()->toDateString(),
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseCount('penghunis', 1);
    }
}
