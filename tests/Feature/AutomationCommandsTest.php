<?php

namespace Tests\Feature;

use App\Mail\KosManagerMail;
use App\Models\Booking;
use App\Models\Kontrak;
use App\Models\Tagihan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AutomationCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_overdue_command_marks_past_due_unpaid_tagihan(): void
    {
        Mail::fake();

        $tagihan = Tagihan::factory()->create([
            'due_date' => today()->subDay(),
            'status' => 'unpaid',
        ]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id, 'status' => 'overdue']);
        Mail::assertQueued(KosManagerMail::class, function ($mail) use ($tagihan) {
            return $mail->mailSubject === 'Tagihan Overdue'
                && str_contains($mail->bodyMessage, $tagihan->bill_number);
        });
    }

    public function test_overdue_command_ignores_paid_and_future_tagihan(): void
    {
        Mail::fake();

        $paid = Tagihan::factory()->create([
            'due_date' => today()->subWeek(),
            'status' => 'paid',
        ]);
        $future = Tagihan::factory()->create([
            'due_date' => today()->addDays(5),
            'status' => 'unpaid',
        ]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tagihans', ['id' => $paid->id, 'status' => 'paid']);
        $this->assertDatabaseHas('tagihans', ['id' => $future->id, 'status' => 'unpaid']);
        Mail::assertNothingQueued();
    }

    public function test_overdue_command_never_sweeps_pending_verification_even_when_past_due(): void
    {
        Mail::fake();

        $tagihan = Tagihan::factory()->create([
            'due_date' => today()->subDay(),
            'status' => 'pending_verification',
        ]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id, 'status' => 'pending_verification']);
        Mail::assertNothingQueued();
    }

    public function test_overdue_command_ignores_current_pending_verification(): void
    {
        Mail::fake();

        $tagihan = Tagihan::factory()->create([
            'due_date' => today()->addDays(2),
            'status' => 'pending_verification',
        ]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id, 'status' => 'pending_verification']);
        Mail::assertNothingQueued();
    }

    public function test_expire_command_expires_approved_booking_past_end_date_without_checkin(): void
    {
        Mail::fake();

        $booking = Booking::factory()->create([
            'status' => 'approved',
            'start_date' => today()->subMonths(2),
            'end_date' => today()->subDay(),
        ]);

        $this->artisan('booking:expire-old')->assertSuccessful();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'expired']);
        Mail::assertQueued(KosManagerMail::class, function ($mail) use ($booking) {
            return $mail->mailSubject === 'Booking Kedaluwarsa'
                && str_contains($mail->bodyMessage, $booking->booking_code);
        });
    }

    public function test_expire_command_ignores_active_approved_booking(): void
    {
        Mail::fake();

        $booking = Booking::factory()->create([
            'status' => 'approved',
            'start_date' => today()->subDay(),
            'end_date' => today()->addMonth(),
        ]);

        $this->artisan('booking:expire-old')->assertSuccessful();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'approved']);
        Mail::assertNothingQueued();
    }

    public function test_remind_command_notifies_tagihan_due_soon(): void
    {
        Mail::fake();

        $tagihan = Tagihan::factory()->create([
            'due_date' => today()->addDays(3),
            'status' => 'unpaid',
        ]);

        $this->artisan('tagihan:remind-due-soon')->assertSuccessful();

        Mail::assertQueued(KosManagerMail::class, function ($mail) use ($tagihan) {
            return $mail->mailSubject === 'Pengingat Jatuh Tempo'
                && str_contains($mail->bodyMessage, $tagihan->bill_number);
        });
    }

    public function test_remind_command_skips_far_future_tagihan(): void
    {
        Mail::fake();

        Tagihan::factory()->create([
            'due_date' => today()->addDays(10),
            'status' => 'unpaid',
        ]);

        $this->artisan('tagihan:remind-due-soon')->assertSuccessful();

        Mail::assertNothingQueued();
    }

    public function test_kontrak_command_expires_active_kontrak_past_end_date(): void
    {
        $kontrak = Kontrak::factory()->create([
            'status' => 'active',
            'end_date' => today()->subDay(),
        ]);
        $stillActive = Kontrak::factory()->create([
            'status' => 'active',
            'end_date' => today()->addMonth(),
        ]);
        $terminated = Kontrak::factory()->create([
            'status' => 'terminated',
            'end_date' => today()->subMonth(),
        ]);

        $this->artisan('kontrak:expire-old')->assertSuccessful();

        $this->assertDatabaseHas('kontraks', ['id' => $kontrak->id, 'status' => 'expired']);
        $this->assertDatabaseHas('kontraks', ['id' => $stillActive->id, 'status' => 'active']);
        $this->assertDatabaseHas('kontraks', ['id' => $terminated->id, 'status' => 'terminated']);
    }
}
