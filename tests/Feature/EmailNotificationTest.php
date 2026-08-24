<?php

namespace Tests\Feature;

use App\Mail\KosManagerMail;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_queues_email_to_user(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        NotificationService::create($user->id, 'test', 'Judul', 'Pesan');

        Mail::assertQueued(KosManagerMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_booking_approved_email_has_subject_and_body(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'tenant']);

        NotificationService::bookingApproved($user->id, 'BK-001');

        Mail::assertQueued(KosManagerMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email)
                && $mail->mailSubject === 'Booking Disetujui'
                && str_contains($mail->bodyMessage, 'BK-001');
        });
    }

    public function test_bill_created_email_contains_due_date(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'tenant']);

        NotificationService::billCreated($user->id, 'TB-001', '31/12/2026');

        Mail::assertQueued(KosManagerMail::class, function ($mail) {
            return $mail->mailSubject === 'Tagihan Baru'
                && str_contains($mail->bodyMessage, 'TB-001')
                && str_contains($mail->bodyMessage, '31/12/2026');
        });
    }

    public function test_checkout_approved_email_sent(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'tenant']);

        NotificationService::checkoutApproved($user->id, '101');

        Mail::assertQueued(KosManagerMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email)
                && $mail->mailSubject === 'Check-Out Disetujui';
        });
    }
}
