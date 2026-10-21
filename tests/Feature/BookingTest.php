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
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl Test No 1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->tenant->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('penghunis', ['user_id' => $this->tenant->id, 'status' => 'active']);
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
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl Test No 1',
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
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl Test No 1',
        ]);

        $response->assertSessionHasErrors('kamar_id');
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
        $response->assertSessionHas('success');

        // Tenant cancel → request pending; booking status tidak berubah
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'approved']);
        $this->assertDatabaseHas('booking_cancellation_requests', [
            'booking_id' => $booking->id,
            'status' => 'pending',
        ]);
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

    public function test_booking_personal_mode_rejects_multiple_occupants(): void
    {
        $kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'available',
            'billing_mode' => 'shared',
            'capacity' => 3,
            'monthly_price' => 1000000,
            'daily_price' => 33334,
            'weekly_price' => 260000,
        ]);

        $response = $this->actingAs($this->tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
            'requested_occupants' => 2,
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl Test No 1',
        ]);

        $response->assertSessionHasErrors('requested_occupants');
    }

    public function test_booking_shared_allows_multiple_occupants(): void
    {
        // Patungan/multi DIHAPUS — shared booking ditolak
        $kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'available',
            'billing_mode' => 'shared',
            'capacity' => 3,
            'monthly_price' => 1000000,
            'daily_price' => 33334,
            'weekly_price' => 260000,
        ]);

        $response = $this->actingAs($this->tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
            'requested_occupants' => 3,
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl Test No 1',
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('bookings', 0);
    }
}
