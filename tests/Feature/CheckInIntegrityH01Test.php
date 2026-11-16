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
 * H-01: check-in hanya boleh menyelesaikan booking setelah payment approved
 * sesuai nominal server dan tepat satu escrow pending yang terhubung ke payment
 * tersebut. Settlement harus sukses sebelum booking menjadi completed.
 *
 * @group escrow
 * @group h01
 */
class CheckInIntegrityH01Test extends TestCase
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
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'booked']);
    }

    private function makeApprovedBooking(array $overrides = []): Booking
    {
        return Booking::factory()->create(array_merge([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => Booking::STATUS_APPROVED,
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
            'identity_data_submitted_at' => now(),
            'identity_confirmed_at' => now(),
            'identity_confirmed_by' => $this->owner->id,
            'start_date' => now()->startOfDay(),
            'end_date' => now()->addMonth(),
            'rental_type' => 'monthly',
            'price' => 1500000,
            'down_payment_amount' => null,
        ], $overrides));
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

    private function checkIn(Booking $booking)
    {
        return $this->actingAs($this->owner)->post(route('owner.checkin.process', $booking), [
            'notes' => 'Check-in H-01',
        ]);
    }

    private function assertNoCheckInArtifacts(Booking $booking): void
    {
        $this->assertSame(Booking::STATUS_APPROVED, $booking->fresh()->status);
        $this->assertDatabaseMissing('penghunis', [
            'user_id' => $booking->user_id,
            'kos_id' => $booking->kos_id,
            'kamar_id' => $booking->kamar_id,
        ]);
        $this->assertDatabaseMissing('kontraks', [
            'kos_id' => $booking->kos_id,
            'kamar_id' => $booking->kamar_id,
        ]);
        $this->assertDatabaseMissing('check_ins', [
            'kamar_id' => $booking->kamar_id,
        ]);
    }

    public function test_checkin_requires_approved_payment(): void
    {
        $booking = $this->makeApprovedBooking();

        $this->checkIn($booking)->assertStatus(400);

        $this->assertNoCheckInArtifacts($booking);
        $this->assertSame(0, WalletTransaction::where('booking_id', $booking->id)->count());
    }

    public function test_checkin_requires_pending_escrow(): void
    {
        $booking = $this->makeApprovedBooking();
        $this->makeApprovedPayment($booking);

        $this->checkIn($booking)->assertStatus(400);

        $this->assertNoCheckInArtifacts($booking);
    }

    public function test_checkin_rejects_duplicate_pending_escrows(): void
    {
        $booking = $this->makeApprovedBooking();
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

        $this->checkIn($booking)->assertStatus(400);

        $this->assertNoCheckInArtifacts($booking);
        $this->assertSame(2, WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'escrow_in')
            ->where('status', 'pending')
            ->count());
    }

    public function test_checkin_rejects_escrow_linked_to_another_bookings_payment(): void
    {
        $booking = $this->makeApprovedBooking();
        $this->makeApprovedPayment($booking);

        $otherBooking = $this->makeApprovedBooking();
        $otherPayment = $this->makeApprovedPayment($otherBooking);

        WalletTransaction::create([
            'owner_id' => $this->owner->id,
            'booking_id' => $booking->id,
            'pembayaran_id' => $otherPayment->id,
            'type' => 'escrow_in',
            'amount' => (int) $booking->expectedBookingPaymentAmount(),
            'description' => 'Escrow tertukar',
            'status' => 'pending',
        ]);

        $this->checkIn($booking)->assertStatus(400);

        $this->assertNoCheckInArtifacts($booking);
    }

    public function test_checkin_rejects_escrow_with_wrong_amount(): void
    {
        $booking = $this->makeApprovedBooking();
        $payment = $this->makeApprovedPayment($booking);

        WalletTransaction::create([
            'owner_id' => $this->owner->id,
            'booking_id' => $booking->id,
            'pembayaran_id' => $payment->id,
            'type' => 'escrow_in',
            'amount' => (int) $booking->expectedBookingPaymentAmount() - 1,
            'description' => 'Escrow nominal salah',
            'status' => 'pending',
        ]);

        $this->checkIn($booking)->assertStatus(400);

        $this->assertNoCheckInArtifacts($booking);
    }

    public function test_successful_checkin_settles_escrow_exactly_once(): void
    {
        $booking = $this->makeApprovedBooking();
        $this->createBookingPaymentWithEscrow($booking);

        $this->checkIn($booking)->assertRedirect(route('owner.booking.index'));

        $this->assertSame(Booking::STATUS_COMPLETED, $booking->fresh()->status);
        $this->assertSame('occupied', $this->kamar->fresh()->status);
        $this->assertDatabaseCount('penghunis', 1);
        $this->assertDatabaseCount('kontraks', 1);
        $this->assertSame(1, CheckIn::where('kamar_id', $this->kamar->id)->count());

        $this->assertSame(1, WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'escrow_in')
            ->where('status', 'pending')
            ->count());
        $this->assertSame(1, WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'commission')
            ->where('status', 'completed')
            ->count());
        $this->assertSame(1, WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'escrow_in')
            ->where('status', 'completed')
            ->count());

        $commission = WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'commission')
            ->firstOrFail();
        $ownerEntitlement = WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'escrow_in')
            ->where('status', 'completed')
            ->firstOrFail();

        $this->assertSame(-75000, $commission->amount);
        $this->assertSame(1425000, $ownerEntitlement->amount);
    }

    public function test_duplicate_checkin_is_rejected_without_extra_settlement(): void
    {
        $booking = $this->makeApprovedBooking();
        $this->createBookingPaymentWithEscrow($booking);

        $this->checkIn($booking)->assertRedirect(route('owner.booking.index'));

        $walletRowsAfterFirst = WalletTransaction::where('booking_id', $booking->id)->count();
        $completedRowsAfterFirst = WalletTransaction::where('booking_id', $booking->id)
            ->where('status', 'completed')
            ->count();

        $this->checkIn($booking)->assertStatus(400);

        $this->assertDatabaseCount('penghunis', 1);
        $this->assertDatabaseCount('kontraks', 1);
        $this->assertSame(1, CheckIn::where('kamar_id', $this->kamar->id)->count());
        $this->assertSame($walletRowsAfterFirst, WalletTransaction::where('booking_id', $booking->id)->count());
        $this->assertSame($completedRowsAfterFirst, WalletTransaction::where('booking_id', $booking->id)
            ->where('status', 'completed')
            ->count());
    }

    public function test_checkin_rolls_back_when_settlement_fails(): void
    {
        $booking = $this->makeApprovedBooking();
        $payment = $this->createBookingPaymentWithEscrow($booking);

        $escrow = WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'escrow_in')
            ->where('status', 'pending')
            ->firstOrFail();

        // Marker komisi parsial memicu inconsistent_settlement pada settlement.
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

        $this->checkIn($booking)->assertStatus(400);

        $this->assertNoCheckInArtifacts($booking);
        $this->assertSame(1, WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'commission')
            ->count());
        $this->assertSame(0, WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'escrow_in')
            ->where('status', 'completed')
            ->count());
    }
}
