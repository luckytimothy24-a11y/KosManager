<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingOccupant;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use App\Services\BookingService;
use App\Services\BookingStatusService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GroupBookingInvitationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $primary;

    private Kos $kos;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->primary = User::factory()->create(['role' => 'tenant', 'email' => 'primary@example.com']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id, 'status' => 'available', 'capacity' => 1,
            'billing_mode' => 'personal', 'monthly_price' => 1200000, 'daily_price' => 50000, 'weekly_price' => 350000,
        ]);
    }

    private function book(?User $user = null): Booking
    {
        $user ??= $this->primary;
        $payload = [
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
            'requested_occupants' => 1,
        ];
        $result = app(BookingService::class)->create($payload, $user->id);
        $this->assertTrue($result['ok'], $result['error'] ?? 'booking failed');

        return $result['booking'];
    }

    private function approve(Booking $booking): Booking
    {
        $pay = app(PaymentService::class)->createForBooking($booking, $this->primary, 'cash');
        $this->assertTrue($pay['ok'], $pay['message'] ?? 'payment failed');
        $this->actingAs($this->owner)->post(route('owner.pembayaran.verify', $pay['pembayaran']));

        $result = app(BookingStatusService::class)->approve($booking, $this->owner);
        $this->assertTrue($result['ok'], $result['error'] ?? 'approve failed');

        return $booking->fresh();
    }

    public function test_invitation_creation_disabled(): void
    {
        $b = $this->book();
        $res = $this->actingAs($this->primary)->postJson(route('tenant.booking.invite', $b), ['email' => 'friend@example.com']);
        $res->assertStatus(403);
    }

    public function test_non_primary_cannot_invite(): void
    {
        $b = $this->book();
        $other = User::factory()->create(['role' => 'tenant', 'email' => 'other@example.com']);
        $res = $this->actingAs($other)->postJson(route('tenant.booking.invite', $b), ['email' => 'friend@example.com']);
        $res->assertStatus(403);
    }

    public function test_owner_cannot_create_invitation(): void
    {
        $b = $this->book();
        $res = $this->actingAs($this->owner)->postJson(route('tenant.booking.invite', $b), ['email' => 'friend@example.com']);
        $res->assertStatus(403);
    }

    public function test_primary_occupant_auto_created(): void
    {
        $b = $this->book();
        $occ = BookingOccupant::where('booking_id', $b->id)->where('occupant_index', 1)->first();
        $this->assertNotNull($occ);
        $this->assertSame('accepted', $occ->status);
        $this->assertSame('pending', $occ->identity_status);
        $this->assertSame((int) $this->primary->id, (int) $occ->user_id);
    }

    public function test_occupants_index_lists_primary(): void
    {
        $b = $this->book();
        $res = $this->actingAs($this->primary)->getJson(route('tenant.booking.occupants.index', $b));
        $res->assertStatus(200);
        $res->assertJsonCount(1, 'data');
        $res->assertJsonPath('data.0.occupant_index', 1);
        $res->assertJsonPath('data.0.status', 'accepted');
    }

    public function test_booking_has_single_requested_occupant(): void
    {
        $b = $this->book();
        $this->assertSame(1, (int) $b->requested_occupants);
    }

    public function test_primary_occupant_identity_status_pending(): void
    {
        $b = $this->book();
        $occ = BookingOccupant::where('booking_id', $b->id)->where('occupant_index', 1)->first();
        $this->assertSame('pending', $occ->identity_status);
    }

    public function test_owner_can_confirm_primary_identity(): void
    {
        $b = $this->approve($this->book());
        $occ = BookingOccupant::where('booking_id', $b->id)->where('occupant_index', 1)->first();
        $token = Str::random(64);
        $occ->update([
            'invite_token_hash' => hash('sha256', $token),
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl Test',
            'identity_data_submitted_at' => now(),
            'identity_status' => BookingOccupant::IDENTITY_PENDING,
        ]);
        $res = $this->actingAs($this->owner)->post(route('owner.booking-occupants.confirm', $occ));
        $res->assertRedirect();
        $this->assertSame('confirmed', $occ->fresh()->identity_status);
    }

    public function test_primary_can_check_in_after_confirmation(): void
    {
        $b = $this->approve($this->book());
        $occ = BookingOccupant::where('booking_id', $b->id)->where('occupant_index', 1)->first();
        $occ->update([
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl Test',
            'identity_data_submitted_at' => now(),
            'identity_status' => BookingOccupant::IDENTITY_CONFIRMED,
            'identity_confirmed_at' => now(),
            'identity_confirmed_by' => $this->owner->id,
        ]);
        $res = $this->actingAs($this->owner)->post(route('owner.booking-occupants.checkin', $occ), ['notes' => 'ok']);
        $res->assertRedirect();
        $occ->refresh();
        $this->assertNotNull($occ->penghuni_id);
        $this->assertSame(1, Penghuni::where('kamar_id', $this->kamar->id)->where('status', 'active')->count());
    }

    public function test_unconfirmed_occupant_cannot_check_in(): void
    {
        $b = $this->approve($this->book());
        $occ = BookingOccupant::where('booking_id', $b->id)->where('occupant_index', 1)->first();
        $occ->update([
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl Test',
            'identity_data_submitted_at' => now(),
            'identity_status' => BookingOccupant::IDENTITY_PENDING,
        ]);
        $res = $this->actingAs($this->owner)->post(route('owner.booking-occupants.checkin', $occ), ['notes' => 'ok']);
        $res->assertStatus(400);
    }

    public function test_occupancy_tracked_correctly_after_checkin(): void
    {
        $b = $this->approve($this->book());
        $occ = BookingOccupant::where('booking_id', $b->id)->where('occupant_index', 1)->first();
        $this->assertSame(0, $this->kamar->fresh()->occupancy());
        $occ->update([
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl Test',
            'identity_data_submitted_at' => now(),
            'identity_status' => BookingOccupant::IDENTITY_CONFIRMED,
            'identity_confirmed_at' => now(),
            'identity_confirmed_by' => $this->owner->id,
        ]);
        $this->actingAs($this->owner)->post(route('owner.booking-occupants.checkin', $occ), ['notes' => 'ok']);
        $this->assertSame(1, $this->kamar->fresh()->occupancy());
    }

    public function test_occupancy_remains_one_after_checkin(): void
    {
        $b = $this->approve($this->book());
        $occ = BookingOccupant::where('booking_id', $b->id)->where('occupant_index', 1)->first();
        $occ->update([
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl Test',
            'identity_data_submitted_at' => now(),
            'identity_status' => BookingOccupant::IDENTITY_CONFIRMED,
            'identity_confirmed_at' => now(),
            'identity_confirmed_by' => $this->owner->id,
        ]);
        $this->actingAs($this->owner)->post(route('owner.booking-occupants.checkin', $occ), ['notes' => 'ok']);
        $this->assertSame(1, $this->kamar->fresh()->occupancy());
    }
}
