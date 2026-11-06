<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\PaymentDistribution;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test Booking Observer: auto-trigger markReadyForBooking on status → approved
 */
class BookingAutoMarkReadyTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Kos $kos;

    private Kamar $kamar;

    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);

        $this->kos = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'active',
        ]);

        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'available',
            'monthly_price' => 2000000,
        ]);

        $this->booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => Booking::STATUS_PENDING,
            'price' => 2000000,
            'start_date' => today()->addDays(3),
            'end_date' => today()->addMonths(2),
            'rental_type' => 'monthly',
        ]);
    }

    // ── Auto-Mark Trigger ────────────────────────────────

    public function test_booking_status_change_to_approved_auto_triggers_mark_ready(): void
    {
        // Create approved payment for booking
        $payment = Pembayaran::factory()->create([
            'booking_id' => $this->booking->id,
            'penghuni_id' => null,
            'amount' => 2000000,
            'payment_date' => today()->toDateString(),
            'verification_status' => 'approved',
        ]);

        // Create payment distribution for booking (prerequisite for markReadyForBooking)
        $distribution = PaymentDistribution::create([
            'booking_id' => $this->booking->id,
            'pembayaran_id' => $payment->id,
            'kos_id' => $this->kos->id,
            'owner_id' => $this->owner->id,
            'gross_amount' => 2000000,
            'platform_fee' => 0,
            'owner_amount' => 2000000,
            'status' => PaymentDistribution::STATUS_PENDING,
        ]);

        // Change booking status to approved
        $this->booking->update(['status' => Booking::STATUS_APPROVED]);
        $this->booking->refresh();

        // Distribution should be marked as 'ready' (via observer)
        $distribution = $this->booking->paymentDistribution;
        $this->assertNotNull($distribution, 'Payment distribution should exist');
        $this->assertSame('ready', $distribution->status);
    }

    public function test_booking_status_change_to_approved_without_payment_does_not_fail(): void
    {
        // No payment created — observer should gracefully skip
        $this->booking->update(['status' => Booking::STATUS_APPROVED]);
        $this->booking->refresh();

        $this->assertSame(Booking::STATUS_APPROVED, $this->booking->status);
        // Should not throw exception
    }

    public function test_booking_observer_only_triggers_on_approved_status(): void
    {
        // Create payment
        $payment = Pembayaran::factory()->create([
            'booking_id' => $this->booking->id,
            'penghuni_id' => null,
            'amount' => 2000000,
            'payment_date' => today()->toDateString(),
            'verification_status' => 'approved',
        ]);

        PaymentDistribution::create([
            'booking_id' => $this->booking->id,
            'pembayaran_id' => $payment->id,
            'kos_id' => $this->kos->id,
            'owner_id' => $this->owner->id,
            'gross_amount' => 2000000,
            'platform_fee' => 0,
            'owner_amount' => 2000000,
            'status' => PaymentDistribution::STATUS_PENDING,
        ]);

        // Change to rejected — should NOT trigger mark ready
        $this->booking->update(['status' => Booking::STATUS_REJECTED]);
        $this->booking->refresh();

        $distribution = $this->booking->paymentDistribution;
        $this->assertNotSame('ready', $distribution->status);
    }

    public function test_booking_status_change_to_completed_does_not_trigger_mark_ready(): void
    {
        $payment = Pembayaran::factory()->create([
            'booking_id' => $this->booking->id,
            'penghuni_id' => null,
            'amount' => 2000000,
            'payment_date' => today()->toDateString(),
            'verification_status' => 'approved',
        ]);

        PaymentDistribution::create([
            'booking_id' => $this->booking->id,
            'pembayaran_id' => $payment->id,
            'kos_id' => $this->kos->id,
            'owner_id' => $this->owner->id,
            'gross_amount' => 2000000,
            'platform_fee' => 0,
            'owner_amount' => 2000000,
            'status' => PaymentDistribution::STATUS_PENDING,
        ]);

        // First approve
        $this->booking->update(['status' => Booking::STATUS_APPROVED]);
        $this->booking->refresh();

        $distribution = $this->booking->paymentDistribution;
        $this->assertSame('ready', $distribution->status);

        // Now transition to completed — should NOT trigger mark ready again (already ready)
        $this->booking->update(['status' => Booking::STATUS_COMPLETED]);
        $this->booking->refresh();

        $distribution = $this->booking->paymentDistribution;
        $this->assertSame('ready', $distribution->status); // Still ready
    }

    public function test_booking_observer_handles_missing_payment_distribution_gracefully(): void
    {
        // Approve booking without distribution existing
        $this->booking->update(['status' => Booking::STATUS_APPROVED]);
        $this->booking->refresh();

        // Should not throw, just skip
        $this->assertSame(Booking::STATUS_APPROVED, $this->booking->status);
    }

    public function test_marking_ready_multiple_times_is_idempotent(): void
    {
        $payment = Pembayaran::factory()->create([
            'booking_id' => $this->booking->id,
            'penghuni_id' => null,
            'amount' => 2000000,
            'payment_date' => today()->toDateString(),
            'verification_status' => 'approved',
        ]);

        PaymentDistribution::create([
            'booking_id' => $this->booking->id,
            'pembayaran_id' => $payment->id,
            'kos_id' => $this->kos->id,
            'owner_id' => $this->owner->id,
            'gross_amount' => 2000000,
            'platform_fee' => 0,
            'owner_amount' => 2000000,
            'status' => PaymentDistribution::STATUS_PENDING,
        ]);

        // First approval
        $this->booking->update(['status' => Booking::STATUS_APPROVED]);
        $this->booking->refresh();

        $distribution = $this->booking->paymentDistribution;
        $this->assertSame('ready', $distribution->status);
        $firstReadyAt = $distribution->ready_at;

        // Update to same status (approved -> approved) - should be idempotent
        $this->booking->update(['status' => Booking::STATUS_APPROVED]);
        $this->booking->refresh();

        $distribution->refresh();
        $this->assertSame('ready', $distribution->status);
        // ready_at should remain the same or be updated without error
    }

    public function test_booking_update_other_fields_does_not_trigger_mark_ready(): void
    {
        $payment = Pembayaran::factory()->create([
            'booking_id' => $this->booking->id,
            'penghuni_id' => null,
            'amount' => 2000000,
            'payment_date' => today()->toDateString(),
            'verification_status' => 'approved',
        ]);

        PaymentDistribution::create([
            'booking_id' => $this->booking->id,
            'pembayaran_id' => $payment->id,
            'kos_id' => $this->kos->id,
            'owner_id' => $this->owner->id,
            'gross_amount' => 2000000,
            'platform_fee' => 0,
            'owner_amount' => 2000000,
            'status' => PaymentDistribution::STATUS_PENDING,
        ]);

        // Update booking notes (not status)
        $this->booking->update(['notes' => 'Updated notes']);
        $this->booking->refresh();

        $distribution = $this->booking->paymentDistribution;
        // Distribution should still be in its original state (pending, not ready)
        $this->assertSame('pending', $distribution->status);
    }

    // ── Invalid Status Transitions ───────────────────────

    public function test_invalid_status_transition_is_blocked(): void
    {
        // Try to go from pending → completed (skip approved)
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('tidak valid');

        $this->booking->update(['status' => Booking::STATUS_COMPLETED]);
    }

    public function test_valid_transition_pending_to_approved_allowed(): void
    {
        Pembayaran::factory()->create([
            'booking_id' => $this->booking->id,
            'penghuni_id' => null,
            'amount' => 2000000,
            'payment_date' => today()->toDateString(),
            'verification_status' => 'approved',
        ]);

        // Should not throw
        $this->booking->update(['status' => Booking::STATUS_APPROVED]);
        $this->booking->refresh();

        $this->assertSame(Booking::STATUS_APPROVED, $this->booking->status);
    }

    public function test_valid_transition_pending_to_rejected_allowed(): void
    {
        // Should not throw
        $this->booking->update(['status' => Booking::STATUS_REJECTED]);
        $this->booking->refresh();

        $this->assertSame(Booking::STATUS_REJECTED, $this->booking->status);
    }

    public function test_valid_transition_pending_to_cancelled_allowed(): void
    {
        // Should not throw
        $this->booking->update(['status' => Booking::STATUS_CANCELLED]);
        $this->booking->refresh();

        $this->assertSame(Booking::STATUS_CANCELLED, $this->booking->status);
    }

    public function test_approved_to_completed_allowed(): void
    {
        Pembayaran::factory()->create([
            'booking_id' => $this->booking->id,
            'penghuni_id' => null,
            'amount' => 2000000,
            'payment_date' => today()->toDateString(),
            'verification_status' => 'approved',
        ]);

        $this->booking->update(['status' => Booking::STATUS_APPROVED]);
        $this->booking->refresh();

        // Now transition to completed
        $this->booking->update(['status' => Booking::STATUS_COMPLETED]);
        $this->booking->refresh();

        $this->assertSame(Booking::STATUS_COMPLETED, $this->booking->status);
    }
}
