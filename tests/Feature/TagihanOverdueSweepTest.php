<?php

namespace Tests\Feature;

use App\Mail\KosManagerMail;
use App\Models\Tagihan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TagihanOverdueSweepTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_verification_past_due_date_is_not_marked_overdue(): void
    {
        Mail::fake();

        $tagihan = Tagihan::factory()->create([
            'due_date' => today()->subDays(7),
            'status' => 'pending_verification',
        ]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tagihans', [
            'id' => $tagihan->id,
            'status' => 'pending_verification',
        ]);
        Mail::assertNothingQueued();
    }

    public function test_unpaid_past_due_date_is_still_marked_overdue(): void
    {
        Mail::fake();

        $tagihan = Tagihan::factory()->create([
            'due_date' => today()->subDay(),
            'status' => 'unpaid',
        ]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tagihans', [
            'id' => $tagihan->id,
            'status' => 'overdue',
        ]);
        Mail::assertQueued(KosManagerMail::class, function ($mail) use ($tagihan) {
            return $mail->mailSubject === 'Tagihan Overdue'
                && str_contains($mail->bodyMessage, $tagihan->bill_number);
        });
    }

    public function test_mixed_batch_only_flips_eligible_unpaid_tagihans(): void
    {
        Mail::fake();

        $eligibleUnpaid = Tagihan::factory()->create([
            'due_date' => today()->subDay(),
            'status' => 'unpaid',
        ]);
        $verifyingPastDue = Tagihan::factory()->create([
            'due_date' => today()->subWeek(),
            'status' => 'pending_verification',
        ]);
        $paidPastDue = Tagihan::factory()->create([
            'due_date' => today()->subMonth(),
            'status' => 'paid',
        ]);
        $futureUnpaid = Tagihan::factory()->create([
            'due_date' => today()->addWeek(),
            'status' => 'unpaid',
        ]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tagihans', ['id' => $eligibleUnpaid->id, 'status' => 'overdue']);
        $this->assertDatabaseHas('tagihans', ['id' => $verifyingPastDue->id, 'status' => 'pending_verification']);
        $this->assertDatabaseHas('tagihans', ['id' => $paidPastDue->id, 'status' => 'paid']);
        $this->assertDatabaseHas('tagihans', ['id' => $futureUnpaid->id, 'status' => 'unpaid']);

        Mail::assertQueued(KosManagerMail::class, 1);
    }
}
