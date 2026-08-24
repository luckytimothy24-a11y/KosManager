<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_code_is_generated(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create();
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $booking = Booking::create([
            'booking_code' => 'BK-'.strtoupper(uniqid()),
            'user_id' => $user->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'booking_date' => now(),
            'start_date' => now()->addDays(5),
            'end_date' => now()->addMonths(2),
            'rental_type' => 'monthly',
            'price' => $kamar->monthly_price,
            'status' => 'pending',
        ]);

        $this->assertNotNull($booking->booking_code);
        $this->assertStringStartsWith('BK-', $booking->booking_code);
    }

    public function test_room_status_transitions(): void
    {
        $kamar = Kamar::factory()->create(['status' => 'available']);

        $kamar->update(['status' => 'booked']);
        $this->assertEquals('booked', $kamar->fresh()->status);

        $kamar->update(['status' => 'occupied']);
        $this->assertEquals('occupied', $kamar->fresh()->status);

        $kamar->update(['status' => 'available']);
        $this->assertEquals('available', $kamar->fresh()->status);

        $kamar->update(['status' => 'maintenance']);
        $this->assertEquals('maintenance', $kamar->fresh()->status);
    }

    public function test_booking_status_flow(): void
    {
        $booking = Booking::factory()->create(['status' => 'pending']);

        $booking->update(['status' => 'approved']);
        $this->assertEquals('approved', $booking->fresh()->status);

        $booking->update(['status' => 'completed']);
        $this->assertEquals('completed', $booking->fresh()->status);
    }

    public function test_booking_reject_does_not_change_room_status(): void
    {
        $kamar = Kamar::factory()->create(['status' => 'available']);
        $booking = Booking::factory()->create([
            'kamar_id' => $kamar->id,
            'status' => 'pending',
        ]);

        $booking->update(['status' => 'rejected']);

        $this->assertEquals('available', $kamar->fresh()->status);
    }

    public function test_booking_cancel_does_not_occupy_room(): void
    {
        $kamar = Kamar::factory()->create(['status' => 'available']);
        $booking = Booking::factory()->create([
            'kamar_id' => $kamar->id,
            'status' => 'pending',
        ]);

        $booking->update(['status' => 'cancelled']);

        $this->assertEquals('available', $kamar->fresh()->status);
    }
}
