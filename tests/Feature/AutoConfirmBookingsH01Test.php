<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CheckIn;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * H-01: scheduler auto-confirm hanya boleh menyelesaikan booking setelah
 * payment approved sesuai nominal server DAN tepat satu escrow pending ada,
 * serta tidak boleh menyisakan booking completed saat settlement gagal.
 *
 * @group escrow
 * @group h01
 */
class AutoConfirmBookingsH01Test extends TestCase
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
        $this->tenant = User::factory()->create(['role' => 'pencari']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'occupied']);
    }

    private function makeEligibleBooking(): Booking
    {
        return Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => Booking::STATUS_APPROVED,
            'price' => 1500000,
            'down_payment_amount' => null,
            'start_date' => now()->subDays(2)->startOfDay(),
            'end_date' => now()->addMonth()->startOfDay(),
        ]);
    }

    private function createCheckInEvidence(Booking $booking): void
    {
        $penghuni = Penghuni::factory()->create([
            'user_id' => $booking->user_id,
            'kos_id' => $booking->kos_id,
            'kamar_id' => $booking->kamar_id,
            'status' => 'active',
        ]);

        Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $booking->kos_id,
            'kamar_id' => $booking->kamar_id,
            'start_date' => $booking->start_date,
            'end_date' => $booking->end_date,
            'status' => 'active',
        ]);

        CheckIn::create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $booking->kamar_id,
            'check_in_date' => now(),
            'check_in_time' => now()->format('H:i'),
            'officer_id' => $this->owner->id,
        ]);
    }

    private function makeApprovedPayment(Booking $booking): Pembayaran
    {
        return Pembayaran::factory()->create([
            'booking_id' => $booking->id,
            'tagihan_id' => null,
            'penghuni_id' => null,
            'amount' => $booking->expectedBookingPaymentAmount(),
            'payment_date' => now(),
            'payment_method' => 'qris',
            'verification_status' => Pembayaran::STATUS_APPROVED,
            'verified_at' => now(),
        ]);
    }

    public function test_auto_confirm_completes_and_settles_eligible_booking_once(): void
    {
        $booking = $this->makeEligibleBooking();
        $this->createCheckInEvidence($booking);
        $this->createBookingPaymentWithEscrow($booking);

        $this->artisan('booking:auto-confirm')
            ->expectsOutput('Auto-confirmed 1 booking(s).')
            ->assertExitCode(0);

        $this->assertSame(Booking::STATUS_COMPLETED, $booking->fresh()->status);
        $this->assertSame(1, WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'commission')
            ->where('status', 'completed')
            ->count());
        $this->assertSame(1, WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'escrow_in')
            ->where('status', 'completed')
            ->count());
        $this->assertSame(1, WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'escrow_in')
            ->where('status', 'pending')
            ->count());

        $settledRows = WalletTransaction::where('booking_id', $booking->id)->count();

        // Rerun tidak boleh membuat settlement kedua.
        $this->artisan('booking:auto-confirm')
            ->expectsOutput('Auto-confirmed 0 booking(s).')
            ->assertExitCode(0);

        $this->assertSame($settledRows, WalletTransaction::where('booking_id', $booking->id)->count());
        $this->assertSame(2, WalletTransaction::where('booking_id', $booking->id)
            ->where('status', 'completed')
            ->count());
    }

    public function test_auto_confirm_skips_booking_without_approved_payment(): void
    {
        $booking = $this->makeEligibleBooking();
        $this->createCheckInEvidence($booking);

        $this->artisan('booking:auto-confirm')->assertExitCode(0);

        $this->assertSame(Booking::STATUS_APPROVED, $booking->fresh()->status);
        $this->assertSame(0, WalletTransaction::where('booking_id', $booking->id)->count());
    }

    public function test_auto_confirm_skips_booking_without_escrow(): void
    {
        $booking = $this->makeEligibleBooking();
        $this->createCheckInEvidence($booking);
        $this->makeApprovedPayment($booking);

        $this->artisan('booking:auto-confirm')->assertExitCode(0);

        $this->assertSame(Booking::STATUS_APPROVED, $booking->fresh()->status);
        $this->assertSame(0, WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'commission')
            ->count());
    }

    public function test_auto_confirm_skips_booking_with_duplicate_escrows(): void
    {
        $booking = $this->makeEligibleBooking();
        $this->createCheckInEvidence($booking);
        $payment = $this->createBookingPaymentWithEscrow($booking);

        WalletTransaction::create([
            'owner_id' => $this->owner->id,
            'booking_id' => $booking->id,
            'pembayaran_id' => $payment->id,
            'type' => 'escrow_in',
            'amount' => (int) $payment->amount,
            'description' => 'Escrow duplikat',
            'status' => 'pending',
        ]);

        $this->artisan('booking:auto-confirm')->assertExitCode(0);

        $this->assertSame(Booking::STATUS_APPROVED, $booking->fresh()->status);
        $this->assertSame(2, WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'escrow_in')
            ->where('status', 'pending')
            ->count());
        $this->assertSame(0, WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'commission')
            ->count());
    }

    public function test_auto_confirm_rolls_back_completion_when_settlement_fails(): void
    {
        $booking = $this->makeEligibleBooking();
        $this->createCheckInEvidence($booking);
        $payment = $this->createBookingPaymentWithEscrow($booking);

        $escrow = WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'escrow_in')
            ->where('status', 'pending')
            ->firstOrFail();

        // Marker komisi parsial membuat settlement fail-closed
        // (inconsistent_settlement), bukan menciptakan entitlement baru.
        WalletTransaction::create([
            'owner_id' => $this->owner->id,
            'booking_id' => $booking->id,
            'pembayaran_id' => $payment->id,
            'type' => 'commission',
            'amount' => -75000,
            'description' => 'Marker komisi parsial',
            'status' => 'completed',
            'metadata' => [
                'settled_from_escrow_id' => $escrow->id,
                'settlement_entry_role' => 'commission',
            ],
        ]);

        $this->artisan('booking:auto-confirm')->assertExitCode(0);

        $this->assertSame(Booking::STATUS_APPROVED, $booking->fresh()->status);
        $this->assertSame(1, WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'commission')
            ->count());
        $this->assertSame(0, WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'escrow_in')
            ->where('status', 'completed')
            ->count());
    }
}
