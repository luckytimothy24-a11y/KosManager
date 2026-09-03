<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingApprovalConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $ownerB;

    private User $tenant;

    private User $tenantB;

    private Kos $kos;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->ownerB = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->tenantB = User::factory()->create(['role' => 'tenant']);

        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'available']);
    }

    private function makePending(User $tenant, int $daysStart = 5, int $daysEnd = 15): Booking
    {
        return Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => Booking::STATUS_PENDING,
            'start_date' => now()->addDays($daysStart),
            'end_date' => now()->addDays($daysEnd),
        ]);
    }

    // ------------------------------------------------------------------
    // 1 & 2 & 3: Two overlapping pending bookings cannot BOTH be approved
    // ------------------------------------------------------------------

    public function test_two_overlapping_pending_bookings_cannot_both_be_approved(): void
    {
        $bookingA = $this->makePending($this->tenant, 5, 15);
        $bookingB = $this->makePending($this->tenantB, 8, 20);

        // First approval succeeds.
        $first = $this->actingAs($this->owner)->post(route('owner.booking.approve', $bookingA));
        $first->assertRedirect();
        $first->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', ['id' => $bookingA->id, 'status' => Booking::STATUS_APPROVED]);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'booked']);

        // Second (overlapping) approval is rejected after kamar/booking re-evaluation.
        $second = $this->actingAs($this->owner)->post(route('owner.booking.approve', $bookingB));
        $second->assertRedirect();
        $second->assertSessionHas('error');

        $this->assertDatabaseHas('bookings', ['id' => $bookingB->id, 'status' => Booking::STATUS_PENDING]);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'booked']);
    }

    // ------------------------------------------------------------------
    // 4: Non-overlapping bookings still work
    // ------------------------------------------------------------------

    public function test_non_overlapping_bookings_on_same_kamar_both_approve(): void
    {
        $bookingA = $this->makePending($this->tenant, 5, 12);
        $bookingB = $this->makePending($this->tenantB, 20, 30);

        $a = $this->actingAs($this->owner)->post(route('owner.booking.approve', $bookingA));
        $a->assertSessionHas('success');

        $b = $this->actingAs($this->owner)->post(route('owner.booking.approve', $bookingB));
        $b->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', ['id' => $bookingA->id, 'status' => Booking::STATUS_APPROVED]);
        $this->assertDatabaseHas('bookings', ['id' => $bookingB->id, 'status' => Booking::STATUS_APPROVED]);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'booked']);
    }

    // ------------------------------------------------------------------
    // 5: Already booked/occupied/maintenance kamar remains protected
    // ------------------------------------------------------------------

    public function test_approval_rejected_when_kamar_already_booked(): void
    {
        $existing = $this->makePending($this->tenant, 5, 15);
        $this->actingAs($this->owner)->post(route('owner.booking.approve', $existing));

        // A second pending booking on the now-booked kamar cannot be approved.
        $second = $this->actingAs($this->owner)->post(route('owner.booking.approve', $this->makePending($this->tenantB, 1, 10)));
        $second->assertSessionHas('error');

        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'booked']);
    }

    public function test_approval_rejected_when_kamar_occupied(): void
    {
        $this->kamar->update(['status' => 'occupied']);

        $booking = $this->makePending($this->tenant);

        $response = $this->actingAs($this->owner)->post(route('owner.booking.approve', $booking));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => Booking::STATUS_PENDING]);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'occupied']);
    }

    public function test_approval_rejected_when_kamar_maintenance(): void
    {
        $this->kamar->update(['status' => 'maintenance']);

        $response = $this->actingAs($this->owner)->post(route('owner.booking.approve', $this->makePending($this->tenant)));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'maintenance']);
    }

    // ------------------------------------------------------------------
    // 6: Owner isolation remains intact
    // ------------------------------------------------------------------

    public function test_owner_cannot_approve_booking_on_another_owners_kos(): void
    {
        $booking = $this->makePending($this->tenant);

        // Owner B (not the kos owner) must be denied.
        $response = $this->actingAs($this->ownerB)->post(route('owner.booking.approve', $booking));
        $response->assertStatus(403);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => Booking::STATUS_PENDING]);
    }

    // ------------------------------------------------------------------
    // 7: Booking already processed cannot be approved twice
    // ------------------------------------------------------------------

    public function test_already_approved_booking_cannot_be_reapproved(): void
    {
        $booking = $this->makePending($this->tenant);
        $this->actingAs($this->owner)->post(route('owner.booking.approve', $booking));

        // Policy re-check denies re-approval (booking no longer pending).
        $second = $this->actingAs($this->owner)->post(route('owner.booking.approve', $booking));
        $second->assertStatus(403);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => Booking::STATUS_APPROVED]);
    }
}
