<?php

namespace Tests\Feature;

use App\Mail\KosManagerMail;
use App\Models\Booking;
use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class P8Batch7CheckoutFutureBookingSchedulerTest extends TestCase
{
    use RefreshDatabase;

    private function createActiveStay(string $kamarStatus = 'occupied'): array
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create([
            'kos_id' => $kos->id,
            'status' => $kamarStatus,
            'monthly_price' => 1500000,
            'daily_price' => 100000,
        ]);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
            'end_date' => now()->addMonths(3),
        ]);

        return [$owner, $tenant, $kos, $kamar, $penghuni, $kontrak];
    }

    private function createPendingCheckout(Penghuni $penghuni, Kamar $kamar): CheckOut
    {
        return CheckOut::create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'request_date' => now(),
            'status' => 'pending',
        ]);
    }

    // =====================================================================
    // CHECKOUT — lifecycle consistency with future reservations
    // =====================================================================

    public function test_checkout_with_future_approved_booking_keeps_kamar_booked(): void
    {
        [$owner, $tenantA, $kos, $kamar, $penghuni, $kontrak] = $this->createActiveStay();

        // Tenant B has an approved future reservation for the same room.
        $tenantB = User::factory()->create(['role' => 'tenant']);
        Booking::factory()->create([
            'user_id' => $tenantB->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
            'start_date' => today()->addMonths(2),
            'end_date' => today()->addMonths(4),
        ]);

        $checkOut = $this->createPendingCheckout($penghuni, $kamar);

        $this->actingAs($owner)->post(route('owner.checkout.approve', $checkOut))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('check_outs', ['id' => $checkOut->id, 'status' => 'approved']);
        $this->assertDatabaseHas('penghunis', ['id' => $penghuni->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('kontraks', ['id' => $kontrak->id, 'status' => 'terminated']);
        // Room must NOT be claimable by a new instant booking: still reserved.
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'booked']);
    }

    public function test_checkout_without_future_booking_frees_kamar_to_available(): void
    {
        [$owner, $tenantA, $kos, $kamar, $penghuni, $kontrak] = $this->createActiveStay();

        $checkOut = $this->createPendingCheckout($penghuni, $kamar);

        $this->actingAs($owner)->post(route('owner.checkout.approve', $checkOut))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'available']);
    }

    public function test_checkout_completed_future_booking_does_not_keep_kamar_booked(): void
    {
        [$owner, $tenantA, $kos, $kamar, $penghuni, $kontrak] = $this->createActiveStay();

        // The tenant's own booking is already completed via check-in — must not
        // be treated as a live reservation that keeps the room booked.
        Booking::factory()->create([
            'user_id' => $tenantA->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'completed',
            'start_date' => today()->subMonth(),
            'end_date' => today()->addMonths(2),
        ]);

        $checkOut = $this->createPendingCheckout($penghuni, $kamar);

        $this->actingAs($owner)->post(route('owner.checkout.approve', $checkOut))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'available']);
    }

    // =====================================================================
    // CHECKOUT — idempotency / duplicate handling
    // =====================================================================

    public function test_duplicate_checkout_request_for_same_active_penghuni_is_rejected(): void
    {
        [$owner, $tenant, $kos, $kamar, $penghuni, $kontrak] = $this->createActiveStay();

        $this->actingAs($tenant)->post(route('tenant.checkout.request', $penghuni))->assertSessionHas('success');

        $response = $this->actingAs($tenant)->post(route('tenant.checkout.request', $penghuni));
        $response->assertSessionHas('error');

        $this->assertDatabaseCount('check_outs', 1);
    }

    public function test_duplicate_checkout_approve_is_rejected(): void
    {
        [$owner, $tenant, $kos, $kamar, $penghuni, $kontrak] = $this->createActiveStay();
        $checkOut = $this->createPendingCheckout($penghuni, $kamar);

        $this->actingAs($owner)->post(route('owner.checkout.approve', $checkOut))->assertSessionHas('success');
        $this->actingAs($owner)->post(route('owner.checkout.approve', $checkOut))->assertStatus(400);

        $this->assertDatabaseHas('check_outs', ['id' => $checkOut->id, 'status' => 'approved']);
        $this->assertDatabaseHas('penghunis', ['id' => $penghuni->id, 'status' => 'inactive']);
    }

    // =====================================================================
    // CHECKOUT — authorization (fail-closed)
    // =====================================================================

    public function test_tenant_cannot_approve_own_checkout(): void
    {
        [$owner, $tenant, $kos, $kamar, $penghuni, $kontrak] = $this->createActiveStay();
        $checkOut = $this->createPendingCheckout($penghuni, $kamar);

        $response = $this->actingAs($tenant)->post(route('owner.checkout.approve', $checkOut));
        $response->assertStatus(403);

        $this->assertDatabaseHas('check_outs', ['id' => $checkOut->id, 'status' => 'pending']);
        $this->assertDatabaseHas('penghunis', ['id' => $penghuni->id, 'status' => 'active']);
    }

    public function test_other_owner_cannot_approve_checkout(): void
    {
        $otherOwner = User::factory()->create(['role' => 'owner']);
        [$owner, $tenant, $kos, $kamar, $penghuni, $kontrak] = $this->createActiveStay();
        $checkOut = $this->createPendingCheckout($penghuni, $kamar);

        $response = $this->actingAs($otherOwner)->post(route('owner.checkout.approve', $checkOut));
        $response->assertStatus(403);

        $this->assertDatabaseHas('check_outs', ['id' => $checkOut->id, 'status' => 'pending']);
    }

    public function test_tenant_cannot_request_checkout_for_another_tenants_penghuni(): void
    {
        [$owner, $tenant, $kos, $kamar, $penghuni, $kontrak] = $this->createActiveStay();
        $otherTenant = User::factory()->create(['role' => 'tenant']);

        $response = $this->actingAs($otherTenant)->post(route('tenant.checkout.request', $penghuni));
        $response->assertStatus(403);

        $this->assertDatabaseCount('check_outs', 0);
    }

    // =====================================================================
    // CHECKOUT — lock-blocking unpaid bills remain intact
    // =====================================================================

    public function test_checkout_blocked_by_unpaid_bill_does_not_change_kamar(): void
    {
        [$owner, $tenant, $kos, $kamar, $penghuni, $kontrak] = $this->createActiveStay();
        $checkOut = $this->createPendingCheckout($penghuni, $kamar);

        Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'status' => 'unpaid',
        ]);

        $this->actingAs($owner)->post(route('owner.checkout.approve', $checkOut))->assertSessionHas('error');

        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'occupied']);
        $this->assertDatabaseHas('penghunis', ['id' => $penghuni->id, 'status' => 'active']);
    }

    // =====================================================================
    // FUTURE BOOKING — date validation & overlap
    // =====================================================================

    public function test_future_booking_succeeds(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create([
            'kos_id' => $kos->id,
            'status' => 'available',
            'monthly_price' => 1500000,
        ]);

        $response = $this->actingAs($tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => today()->addDays(10)->format('Y-m-d'),
            'end_date' => today()->addDays(40)->format('Y-m-d'),
            'rental_type' => 'monthly',
        ])->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'user_id' => $tenant->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'booked']);
    }

    public function test_past_start_date_is_rejected(): void
    {
        [$owner, $tenant, $kos, $kamar, $penghuni, $kontrak] = $this->createActiveStay('available');

        $response = $this->actingAs($tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => today()->subDay()->format('Y-m-d'),
            'end_date' => today()->addDays(10)->format('Y-m-d'),
            'rental_type' => 'monthly',
        ]);

        $response->assertSessionHasErrors(['start_date']);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_end_date_not_after_start_date_is_rejected(): void
    {
        [$owner, $tenant, $kos, $kamar, $penghuni, $kontrak] = $this->createActiveStay('available');

        $response = $this->actingAs($tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => today()->addDays(10)->format('Y-m-d'),
            'end_date' => today()->addDays(10)->format('Y-m-d'),
            'rental_type' => 'monthly',
        ]);

        $response->assertSessionHasErrors(['end_date']);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_overlapping_future_booking_is_rejected(): void
    {
        [$owner, $tenant, $kos, $kamar, $penghuni, $kontrak] = $this->createActiveStay('available');

        // Existing approved future booking 2026-10-10 .. 2026-10-20.
        Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-20',
        ]);
        $kamar->update(['status' => 'booked']);

        // Requested 2026-10-15 .. 2026-10-25 overlaps -> REJECT.
        $response = $this->actingAs($tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => '2026-10-15',
            'end_date' => '2026-10-25',
            'rental_type' => 'monthly',
        ]);

        $response->assertSessionHasErrors('kamar_id');
        $this->assertFalse(Booking::where('user_id', $tenant->id)
            ->where('start_date', '2026-10-15')
            ->exists());
    }

    public function test_booked_room_with_future_reservation_cannot_accept_new_instant_booking(): void
    {
        [$owner, $tenant, $kos, $kamar, $penghuni, $kontrak] = $this->createActiveStay('booked');

        Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-20',
        ]);

        $tenantB = User::factory()->create(['role' => 'tenant']);
        $response = $this->actingAs($tenantB)->post(route('tenant.booking.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-20',
            'rental_type' => 'monthly',
        ]);

        $response->assertSessionHasErrors('kamar_id');
        $this->assertDatabaseMissing('bookings', ['user_id' => $tenantB->id]);
    }

    public function test_maintenance_room_cannot_be_booked_in_future(): void
    {
        [$owner, $tenant, $kos, $kamar, $penghuni, $kontrak] = $this->createActiveStay('maintenance');

        $response = $this->actingAs($tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => today()->addDays(5)->format('Y-m-d'),
            'end_date' => today()->addDays(20)->format('Y-m-d'),
            'rental_type' => 'monthly',
        ]);

        $response->assertSessionHasErrors('kamar_id');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_occupied_room_cannot_be_booked_in_future(): void
    {
        [$owner, $tenant, $kos, $kamar, $penghuni, $kontrak] = $this->createActiveStay('occupied');

        $response = $this->actingAs($tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => today()->addDays(5)->format('Y-m-d'),
            'end_date' => today()->addDays(20)->format('Y-m-d'),
            'rental_type' => 'monthly',
        ]);

        $response->assertSessionHasErrors('kamar_id');
        $this->assertDatabaseCount('bookings', 0);
    }

    // =====================================================================
    // SCHEDULER — auto-checkout keeps future-reserved room booked + idempotency
    // =====================================================================

    public function test_auto_checkout_with_future_booking_keeps_kamar_booked(): void
    {
        Mail::fake();

        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'occupied']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
            'end_date' => today()->subDay(),
        ]);

        $tenantB = User::factory()->create(['role' => 'tenant']);
        Booking::factory()->create([
            'user_id' => $tenantB->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
            'start_date' => today()->addDays(20),
            'end_date' => today()->addDays(50),
        ]);

        $this->artisan('kontrak:expire-old')->assertSuccessful();

        $this->assertDatabaseHas('penghunis', ['id' => $penghuni->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'booked']);
        $this->assertDatabaseCount('check_outs', 1);
    }

    public function test_auto_checkout_is_idempotent_and_does_not_create_duplicates(): void
    {
        Mail::fake();

        [$owner, $tenant, $kos, $kamar, $penghuni, $kontrak] = $this->createActiveStay();
        $kontrak->update(['end_date' => today()->subDay()]);

        $this->artisan('kontrak:expire-old')->assertSuccessful();
        $this->artisan('kontrak:expire-old')->assertSuccessful();

        $this->assertDatabaseCount('check_outs', 1);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'available']);
    }

    public function test_expire_booking_command_is_idempotent_with_future_reservation(): void
    {
        Mail::fake();

        [$owner, $tenant, $kos, $kamar, $penghuni, $kontrak] = $this->createActiveStay('booked');
        $staleBooking = Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
            'start_date' => today()->subDays(30),
            'end_date' => today()->subDay(),
        ]);

        $tenantB = User::factory()->create(['role' => 'tenant']);
        Booking::factory()->create([
            'user_id' => $tenantB->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
            'start_date' => today()->addDay(),
            'end_date' => today()->addMonth(),
        ]);

        $this->artisan('booking:expire-old')->assertSuccessful();
        $this->artisan('booking:expire-old')->assertSuccessful();

        $this->assertDatabaseHas('bookings', ['id' => $staleBooking->id, 'status' => 'expired']);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'booked']);
        Mail::assertQueued(KosManagerMail::class, 1);
    }
}
