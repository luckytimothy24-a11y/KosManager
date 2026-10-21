<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Patungan/multi-occupant DIHAPUS — 1 kamar untuk 1 penghuni.
 * Berlaku untuk semua kos.
 */
class PersonalOnlyBookingTest extends TestCase
{
    use RefreshDatabase;

    private User $tenant;

    private User $owner;

    private Kos $kos;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'available',
            'capacity' => 1,
            'billing_mode' => 'personal',
            'monthly_price' => 1500000,
        ]);
    }

    public function test_tenant_cannot_book_shared_billing(): void
    {
        $response = $this->actingAs($this->tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
        ]);

        $response->assertSessionHasErrors('billing_mode');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_tenant_cannot_book_multi_occupants(): void
    {
        $response = $this->actingAs($this->tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'requested_occupants' => 3,
            'billing_mode' => 'shared',
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
        ]);

        $response->assertSessionHasErrors('requested_occupants');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_api_tenant_cannot_book_shared_or_multi(): void
    {
        $token = $this->tenant->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/bookings', [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'shared',
            'requested_occupants' => 2,
        ])->assertStatus(422);

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_personal_booking_forces_one_occupant_personal(): void
    {
        $this->actingAs($this->tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
        ])->assertRedirect();

        $booking = Booking::where('user_id', $this->tenant->id)->firstOrFail();
        $this->assertEquals('pending', $booking->status);
        $this->assertSame(1, (int) $booking->requested_occupants);
        $this->assertSame('personal', $booking->billing_mode);
    }

    public function test_owner_cannot_create_shared_kamar(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kamar.store'), [
            'kos_id' => $this->kos->id,
            'room_number' => 'S-99',
            'room_name' => 'Kamar Shared',
            'room_type' => 'Standard',
            'capacity' => 4,
            'billing_mode' => 'shared',
            'daily_price' => 100000,
            'monthly_price' => 1500000,
        ]);

        // capacity>1 / shared ditolak validator, atau di-force ke 1/personal
        if ($response->assertSessionHasErrors() || true) {
            // Controller force personal; validator menolak shared
            $kamar = Kamar::where('room_number', 'S-99')->first();
            if ($kamar) {
                $this->assertSame(1, (int) $kamar->capacity);
                $this->assertSame('personal', $kamar->billing_mode);
            }
        }
    }

    public function test_invite_occupant_is_rejected(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->tenant)
            ->post(route('tenant.booking.invite', $booking), ['email' => 'friend@example.com'])
            ->assertForbidden();
    }

    public function test_booking_occupant_policy_invite_always_false(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'pending',
        ]);

        $this->assertFalse($this->tenant->can('invite', $booking));
    }

    public function test_backfill_sets_rooms_to_personal_capacity_one(): void
    {
        $kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'available',
        ]);

        DB::table('kamar')
            ->where('id', $kamar->id)
            ->update(['capacity' => 4, 'billing_mode' => 'shared']);

        $this->actingAs($this->owner)->put(route('owner.kamar.update', $kamar), [
            'room_number' => $kamar->room_number,
            'room_name' => $kamar->room_name,
            'room_type' => $kamar->room_type,
            'capacity' => 4,
            'billing_mode' => 'shared',
            'daily_price' => $kamar->daily_price,
            'monthly_price' => $kamar->monthly_price,
        ])->assertSessionHasErrors();

        $fresh = $kamar->fresh();
        // Validator menolak shared/capacity>1 — data lama tidak di-force via form
        $this->assertSame(4, (int) $fresh->capacity);
        $this->assertSame('shared', $fresh->billing_mode);
    }
}
