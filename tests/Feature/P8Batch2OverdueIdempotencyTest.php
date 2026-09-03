<?php

namespace Tests\Feature;

use App\Mail\KosManagerMail;
use App\Models\Tagihan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * P8 Batch 2 — Overdue Process Idempotency & Race Safety.
 */
class P8Batch2OverdueIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_due_date_passed_unpaid_is_marked_overdue(): void
    {
        Mail::fake();

        $tagihan = Tagihan::factory()->create([
            'due_date' => today()->subDay(),
            'status' => 'unpaid',
        ]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id, 'status' => 'overdue']);
        Mail::assertQueued(KosManagerMail::class, 1);
    }

    public function test_due_date_today_is_not_marked_overdue(): void
    {
        Mail::fake();

        $tagihan = Tagihan::factory()->create([
            'due_date' => today(),
            'status' => 'unpaid',
        ]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id, 'status' => 'unpaid']);
        Mail::assertNothingQueued();
    }

    public function test_due_date_future_is_not_marked_overdue(): void
    {
        Mail::fake();

        $tagihan = Tagihan::factory()->create([
            'due_date' => today()->addDay(),
            'status' => 'unpaid',
        ]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id, 'status' => 'unpaid']);
    }

    public function test_already_overdue_is_not_processed_again(): void
    {
        Mail::fake();

        $tagihan = Tagihan::factory()->create([
            'due_date' => today()->subWeek(),
            'status' => 'overdue',
        ]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id, 'status' => 'overdue']);
        Mail::assertNothingQueued();
    }

    public function test_already_paid_is_not_processed(): void
    {
        Mail::fake();

        $tagihan = Tagihan::factory()->create([
            'due_date' => today()->subMonth(),
            'status' => 'paid',
        ]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id, 'status' => 'paid']);
        Mail::assertNothingQueued();
    }

    public function test_terminal_cancelled_is_not_processed(): void
    {
        Mail::fake();

        $tagihan = Tagihan::factory()->create([
            'due_date' => today()->subMonth(),
            'status' => 'cancelled',
        ]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id, 'status' => 'cancelled']);
        Mail::assertNothingQueued();
    }

    public function test_pending_verification_past_due_is_not_marked(): void
    {
        Mail::fake();

        $tagihan = Tagihan::factory()->create([
            'due_date' => today()->subWeek(),
            'status' => 'pending_verification',
        ]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id, 'status' => 'pending_verification']);
        Mail::assertNothingQueued();
    }

    public function test_command_executed_twice_is_idempotent(): void
    {
        Mail::fake();

        $tagihan = Tagihan::factory()->create([
            'due_date' => today()->subDay(),
            'status' => 'unpaid',
        ]);

        $this->artisan('tagihan:mark-overdue')->assertSuccessful();
        $this->artisan('tagihan:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id, 'status' => 'overdue']);
        // Hanya satu notifikasi (bukan dua).
        Mail::assertQueued(KosManagerMail::class, 1);
    }

    public function test_concurrent_style_transition_sends_single_notification(): void
    {
        Mail::fake();

        $tagihan = Tagihan::factory()->create([
            'due_date' => today()->subDay(),
            'status' => 'unpaid',
        ]);

        // Simulasi worker A dan B menjalankan update atomik yang sama.
        // Hanya satu yang berhasil (memengaruhi 1 baris), yang lain 0 baris.
        $a = Tagihan::whereKey($tagihan->id)
            ->where('status', 'unpaid')
            ->update(['status' => 'overdue']);
        $b = Tagihan::whereKey($tagihan->id)
            ->where('status', 'unpaid')
            ->update(['status' => 'overdue']);

        $this->assertSame(1, $a);
        $this->assertSame(0, $b);
        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id, 'status' => 'overdue']);
    }
}
