<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Penghuni;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KamarStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_maintenance_remains_maintenance(): void
    {
        $kamar = Kamar::factory()->create(['status' => 'maintenance']);
        $kamar->syncStatus();
        $this->assertEquals('maintenance', $kamar->refresh()->status);
    }

    public function test_active_penghuni_makes_occupied(): void
    {
        $kamar = Kamar::factory()->create();
        $penghuni = Penghuni::factory()->state(['kamar_id' => $kamar->id, 'status' => 'active'])->create();
        $kamar->syncStatus();
        $this->assertEquals('occupied', $kamar->refresh()->status);
    }

    public function test_approved_booking_end_tomorrow_booked(): void
    {
        $kamar = Kamar::factory()->create();
        $booking = Booking::factory()->approved()
            ->state(['kamar_id' => $kamar->id, 'end_date' => now()->addDay()])
            ->create();
        $kamar->syncStatus();
        $this->assertEquals('booked', $kamar->refresh()->status);
    }

    public function test_approved_booking_end_today_booked(): void
    {
        $kamar = Kamar::factory()->create();
        $booking = Booking::factory()->approved()
            ->state(['kamar_id' => $kamar->id, 'end_date' => today()])
            ->create();
        $kamar->syncStatus();
        $this->assertEquals('booked', $kamar->refresh()->status);
    }

    public function test_approved_booking_end_yesterday_available(): void
    {
        $kamar = Kamar::factory()->create();
        $booking = Booking::factory()->approved()
            ->state(['kamar_id' => $kamar->id, 'end_date' => now()->subDay()])
            ->create();
        $kamar->syncStatus();
        $this->assertEquals('available', $kamar->refresh()->status);
    }

    public function test_no_penghuni_no_booking_available(): void
    {
        $kamar = Kamar::factory()->create(); // no penghuni, no booking
        $kamar->syncStatus();
        $this->assertEquals('available', $kamar->refresh()->status);
    }

    public function test_multiple_penghuni_one_active_occupied(): void
    {
        $kamar = Kamar::factory()->create();
        Penghuni::factory()->state(['kamar_id' => $kamar->id, 'status' => 'active'])->create();
        Penghuni::factory()->state(['kamar_id' => $kamar->id, 'status' => 'inactive'])->create();
        $kamar->syncStatus();
        $this->assertEquals('occupied', $kamar->refresh()->status);
    }

    public function test_maintenance_with_active_penghuni_remains_maintenance(): void
    {
        $kamar = Kamar::factory()->create(['status' => 'maintenance']);
        $penghuni = Penghuni::factory()->state(['kamar_id' => $kamar->id, 'status' => 'active'])->create();
        $kamar->syncStatus();
        $this->assertEquals('maintenance', $kamar->refresh()->status);
    }
}
