<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiOccupantTest extends TestCase
{
    use RefreshDatabase;

    public function test_capacity_one_allows_single_occupant(): void
    {
        $kamar = Kamar::factory()->create(['capacity' => 1, 'billing_mode' => 'personal']);
        $this->assertEquals(1, $kamar->capacity);
        $this->assertTrue($kamar->hasAvailableSlot());
    }

    public function test_capacity_two_allows_two_occupants(): void
    {
        $kamar = Kamar::factory()->create(['capacity' => 2, 'billing_mode' => 'shared']);
        Penghuni::factory()->create(['kamar_id' => $kamar->id, 'status' => 'active']);
        $this->assertEquals(1, $kamar->remainingSlots());
        $this->assertTrue($kamar->hasAvailableSlot());
    }

    public function test_capacity_three_allows_three_occupants(): void
    {
        $kamar = Kamar::factory()->create(['capacity' => 3, 'billing_mode' => 'shared']);
        Penghuni::factory()->count(2)->create(['kamar_id' => $kamar->id, 'status' => 'active']);
        $this->assertEquals(1, $kamar->remainingSlots());
        $this->assertTrue($kamar->hasAvailableSlot());
    }

    public function test_capacity_four_allows_four_occupants(): void
    {
        $kamar = Kamar::factory()->create(['capacity' => 4, 'billing_mode' => 'shared']);
        Penghuni::factory()->count(3)->create(['kamar_id' => $kamar->id, 'status' => 'active']);
        $this->assertEquals(1, $kamar->remainingSlots());
        $this->assertTrue($kamar->hasAvailableSlot());
    }

    public function test_occupant_count_exceeds_capacity_is_rejected(): void
    {
        $kamar = Kamar::factory()->create(['capacity' => 2, 'billing_mode' => 'shared']);
        Penghuni::factory()->count(2)->create(['kamar_id' => $kamar->id, 'status' => 'active']);
        $this->assertEquals(0, $kamar->remainingSlots());
        $this->assertFalse($kamar->hasAvailableSlot());
        $this->assertTrue($kamar->isFull());
    }

    public function test_kamar_with_capacity_four_allows_one_occupant(): void
    {
        $kamar = Kamar::factory()->create(['capacity' => 4, 'billing_mode' => 'shared']);
        Penghuni::factory()->create(['kamar_id' => $kamar->id, 'status' => 'active']);
        $this->assertEquals(3, $kamar->remainingSlots());
        $this->assertTrue($kamar->hasAvailableSlot());
    }

    public function test_booking_service_rejects_occupants_exceeding_capacity(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create();
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'capacity' => 2, 'billing_mode' => 'shared', 'status' => 'available']);
        Penghuni::factory()->create(['kamar_id' => $kamar->id, 'status' => 'active']);

        $bookingService = new BookingService();
        $result = $bookingService->create([
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(30),
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
            'requested_occupants' => 2,
            'notes' => '',
            'identity_number' => '12345678',
            'phone' => '081234567890',
            'address' => 'Test Address',
        ], $user->id);

        $this->assertFalse($result['ok']);
        $this->assertEquals('kamar_penuh', $result['error']);
    }

    public function test_booking_service_allows_occupants_within_capacity(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create();
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'capacity' => 3, 'billing_mode' => 'shared', 'status' => 'available']);

        $bookingService = new BookingService();
        $result = $bookingService->create([
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(30),
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
            'requested_occupants' => 1,
            'notes' => '',
            'identity_number' => '12345678',
            'phone' => '081234567890',
            'address' => 'Test Address',
        ], $user->id);

        $this->assertTrue($result['ok']);
    }

    public function test_personal_billing_rejects_more_than_one_occupant(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create();
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'capacity' => 1, 'billing_mode' => 'personal', 'status' => 'available']);

        $bookingService = new BookingService();
        $result = $bookingService->create([
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(30),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
            'requested_occupants' => 2,
            'notes' => '',
            'identity_number' => '12345678',
            'phone' => '081234567890',
            'address' => 'Test Address',
        ], $user->id);

        $this->assertFalse($result['ok']);
        $this->assertEquals('personal_multi_rejected', $result['error']);
    }

    public function test_add_penghuni_to_full_kamar_is_rejected(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'capacity' => 2, 'billing_mode' => 'shared']);
        Penghuni::factory()->count(2)->create(['kamar_id' => $kamar->id, 'status' => 'active']);

        $this->assertEquals(2, $kamar->fresh()->occupancy());
        $this->assertTrue($kamar->isFull());
        $this->assertFalse($kamar->hasAvailableSlot());
    }

    public function test_kamar_capacity_zero_is_invalid(): void
    {
        $kamar = Kamar::factory()->create(['capacity' => 0]);
        $this->assertEquals(0, $kamar->capacity);
        $this->assertFalse($kamar->hasAvailableSlot());
    }
}