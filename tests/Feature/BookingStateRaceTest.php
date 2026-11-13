<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingStateRaceTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Kos $kos;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'pencari']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'available']);
    }

    public function test_double_cancel_request_creates_single_pending_request(): void
    {
        $this->kamar->update(['status' => 'booked']);

        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'approved',
        ]);

        $first = $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking));
        $first->assertRedirect();
        $first->assertSessionHas('success');

        // Booking belum dibatalkan: menunggu approval owner. Reservasi tetap dipegang.
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'approved']);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'booked']);
        $this->assertDatabaseCount('booking_cancellation_requests', 1);

        $second = $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking));

        $second->assertRedirect();
        $second->assertSessionHas('error');

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'approved']);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'booked']);
        $this->assertDatabaseCount('booking_cancellation_requests', 1);
    }

    public function test_completed_booking_cannot_be_cancelled_by_tenant(): void
    {
        $this->kamar->update(['status' => 'occupied']);

        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking));

        $response->assertStatus(403);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'completed']);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'occupied']);
    }
}
