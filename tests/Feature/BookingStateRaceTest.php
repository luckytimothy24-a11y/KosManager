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
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'available']);
    }

    public function test_double_cancel_frees_kamar_once_and_second_attempt_is_blocked(): void
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

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'available']);

        $second = $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking));

        $second->assertStatus(403);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'available']);
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
