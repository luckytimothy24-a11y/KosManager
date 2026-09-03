<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
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

    public function test_tenant_can_create_booking(): void
    {
        $response = $this->actingAs($this->tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'notes' => 'Mau kosong',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->tenant->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'approved',
        ]);
    }

    public function test_cannot_book_occupied_room(): void
    {
        $this->kamar->update(['status' => 'occupied']);

        $response = $this->actingAs($this->tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
        ]);

        $response->assertSessionHasErrors('kamar_id');
    }

    public function test_cannot_book_maintenance_room(): void
    {
        $this->kamar->update(['status' => 'maintenance']);

        $response = $this->actingAs($this->tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
        ]);

        $response->assertSessionHasErrors('kamar_id');
    }

    public function test_tenant_cannot_approve_booking(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->tenant)->post(route('owner.booking.approve', $booking));
        $response->assertStatus(403);
    }

    public function test_owner_can_approve_booking(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.booking.approve', $booking));
        $response->assertRedirect(route('owner.booking.index'));

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'approved']);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'booked']);
    }

    public function test_owner_can_reject_booking(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.booking.reject', $booking));
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'rejected']);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'available']);
    }

    public function test_tenant_can_cancel_own_booking(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking));
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'cancelled']);
    }

    public function test_tenant_cannot_cancel_other_booking(): void
    {
        $otherTenant = User::factory()->create(['role' => 'tenant']);
        $booking = Booking::factory()->create([
            'user_id' => $otherTenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->tenant)->post(route('tenant.booking.cancel', $booking));
        $response->assertStatus(403);
    }

    public function test_owner_can_view_booking_detail(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
        ]);

        $response = $this->actingAs($this->owner)->get(route('owner.booking.show', $booking));
        $response->assertStatus(200);
    }

    public function test_tenant_can_view_own_bookings(): void
    {
        Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.booking.index'));
        $response->assertStatus(200);
    }

    public function test_cannot_approve_overlapping_approved_booking(): void
    {
        $tenantB = User::factory()->create(['role' => 'tenant']);

        $bookingA = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(30),
            'status' => 'pending',
        ]);
        $bookingB = Booking::factory()->create([
            'user_id' => $tenantB->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
            'status' => 'pending',
        ]);

        $this->actingAs($this->owner)->post(route('owner.booking.approve', $bookingA))->assertRedirect();
        $this->assertDatabaseHas('bookings', ['id' => $bookingA->id, 'status' => 'approved']);

        $response = $this->actingAs($this->owner)->post(route('owner.booking.approve', $bookingB));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('bookings', ['id' => $bookingB->id, 'status' => 'pending']);
    }
}
