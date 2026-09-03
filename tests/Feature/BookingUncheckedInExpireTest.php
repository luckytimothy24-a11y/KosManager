<?php

namespace Tests\Feature;

use App\Mail\KosManagerMail;
use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BookingUncheckedInExpireTest extends TestCase
{
    use RefreshDatabase;

    private function createApprovedStay(string $kamarStatus = 'booked', array $bookingAttributes = []): array
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => $kamarStatus]);

        $booking = Booking::factory()->create(array_merge([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
            'end_date' => today()->subDay(),
        ], $bookingAttributes));

        return [$owner, $tenant, $kamar, $booking];
    }

    public function test_approved_booking_past_end_date_without_checkin_expires_and_frees_kamar(): void
    {
        Mail::fake();

        [$owner, $tenant, $kamar, $booking] = $this->createApprovedStay('booked', [
            'start_date' => today()->subDays(30),
        ]);

        $this->artisan('booking:expire-old')->assertSuccessful();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'expired']);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'available']);

        Mail::assertQueued(KosManagerMail::class, function ($mail) use ($tenant) {
            return $mail->mailSubject === 'Booking Kedaluwarsa'
                && $mail->to[0]['address'] === $tenant->email;
        });
    }

    public function test_completed_booking_is_never_swept(): void
    {
        Mail::fake();

        [$owner, $tenant, $kamar, $booking] = $this->createApprovedStay('occupied', [
            'start_date' => today()->subDays(60),
            'status' => 'completed',
        ]);

        $this->artisan('booking:expire-old')->assertSuccessful();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'completed']);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'occupied']);
        Mail::assertNothingQueued();
    }

    public function test_kamar_stays_booked_when_another_approved_reservation_covers_the_future(): void
    {
        Mail::fake();

        [$owner, $tenantA, $kamar, $staleBooking] = $this->createApprovedStay('booked', [
            'start_date' => today()->subDays(30),
        ]);

        $tenantB = User::factory()->create(['role' => 'tenant']);

        Booking::factory()->create([
            'user_id' => $tenantB->id,
            'kos_id' => $kamar->kos_id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
            'start_date' => today(),
            'end_date' => today()->addMonth(),
        ]);

        $this->artisan('booking:expire-old')->assertSuccessful();

        $this->assertDatabaseHas('bookings', ['id' => $staleBooking->id, 'status' => 'expired']);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'booked']);
    }

    public function test_future_approved_booking_is_not_touched(): void
    {
        Mail::fake();

        [$owner, $tenant, $kamar, $booking] = $this->createApprovedStay('booked', [
            'start_date' => today()->addDay(),
            'end_date' => today()->addMonth(),
        ]);

        $this->artisan('booking:expire-old')->assertSuccessful();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'approved']);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'booked']);
        Mail::assertNothingQueued();
    }
}
