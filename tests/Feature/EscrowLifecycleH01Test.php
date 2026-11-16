<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\EscrowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * H-01: escrow hanya dibuat dari payment approved dengan nominal server yang
 * benar, tidak pernah duplikat, dan settlement bersifat idempotent.
 *
 * @group escrow
 * @group h01
 */
class EscrowLifecycleH01Test extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Kos $kos;

    private Kamar $kamar;

    private EscrowService $escrowService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);
        $this->escrowService = app(EscrowService::class);
    }

    private function makeBooking(array $overrides = []): Booking
    {
        return Booking::factory()->create(array_merge([
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => Booking::STATUS_APPROVED,
            'price' => 1500000,
            'down_payment_amount' => null,
        ], $overrides));
    }

    private function makeApprovedPayment(Booking $booking, ?float $amount = null): Pembayaran
    {
        return Pembayaran::factory()->create([
            'booking_id' => $booking->id,
            'tagihan_id' => null,
            'penghuni_id' => null,
            'amount' => $amount ?? $booking->expectedBookingPaymentAmount(),
            'payment_date' => now(),
            'payment_method' => 'qris',
            'verification_status' => Pembayaran::STATUS_APPROVED,
            'verified_at' => now(),
        ]);
    }

    private function makeEscrow(Pembayaran $payment, array $overrides = []): WalletTransaction
    {
        return WalletTransaction::create(array_merge([
            'owner_id' => $this->owner->id,
            'booking_id' => $payment->booking_id,
            'pembayaran_id' => $payment->id,
            'type' => 'escrow_in',
            'amount' => (int) $payment->amount,
            'description' => 'Escrow test',
            'status' => 'pending',
        ], $overrides));
    }

    public function test_hold_escrow_creates_exactly_one_pending_escrow_for_approved_payment(): void
    {
        $booking = $this->makeBooking();
        $payment = $this->makeApprovedPayment($booking);

        $escrow = $this->escrowService->holdEscrow($payment);

        $this->assertNotNull($escrow);
        $this->assertSame('pending', $escrow->status);
        $this->assertSame((int) $booking->id, (int) $escrow->booking_id);
        $this->assertSame((int) $payment->id, (int) $escrow->pembayaran_id);
        $this->assertSame((int) $this->owner->id, (int) $escrow->owner_id);
        $this->assertSame((int) $payment->amount, $escrow->amount);
        $this->assertSame((int) $payment->amount, $escrow->metadata['gross_amount']);
        $this->assertSame(1, WalletTransaction::where('pembayaran_id', $payment->id)
            ->where('type', 'escrow_in')
            ->count());
    }

    public function test_hold_escrow_is_idempotent_for_same_payment(): void
    {
        $booking = $this->makeBooking();
        $payment = $this->makeApprovedPayment($booking);

        $first = $this->escrowService->holdEscrow($payment);
        $second = $this->escrowService->holdEscrow($payment);

        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertSame((int) $first->id, (int) $second->id);
        $this->assertSame(1, WalletTransaction::where('pembayaran_id', $payment->id)
            ->where('type', 'escrow_in')
            ->count());
    }

    public function test_hold_escrow_uses_down_payment_amount_when_present(): void
    {
        $booking = $this->makeBooking(['down_payment_amount' => 300000]);
        $payment = $this->makeApprovedPayment($booking, 300000);

        $escrow = $this->escrowService->holdEscrow($payment);

        $this->assertNotNull($escrow);
        $this->assertSame(300000, $escrow->amount);
    }

    public function test_hold_escrow_rejects_payment_with_wrong_amount(): void
    {
        $booking = $this->makeBooking();
        $payment = $this->makeApprovedPayment($booking, $booking->expectedBookingPaymentAmount() + 1);

        $this->assertNull($this->escrowService->holdEscrow($payment));
        $this->assertSame(0, WalletTransaction::where('pembayaran_id', $payment->id)->count());
    }

    public function test_hold_escrow_rejects_terminal_bookings(): void
    {
        foreach ([
            Booking::STATUS_REJECTED,
            Booking::STATUS_CANCELLED,
            Booking::STATUS_EXPIRED,
            Booking::STATUS_COMPLETED,
        ] as $status) {
            $booking = $this->makeBooking(['status' => $status]);
            $payment = $this->makeApprovedPayment($booking);

            $this->assertNull(
                $this->escrowService->holdEscrow($payment),
                "Escrow tidak boleh dibuat untuk booking berstatus {$status}."
            );
            $this->assertSame(0, WalletTransaction::where('pembayaran_id', $payment->id)->count());
        }
    }

    public function test_hold_escrow_accepts_non_terminal_bookings(): void
    {
        foreach ([Booking::STATUS_PENDING, Booking::STATUS_APPROVED] as $status) {
            $booking = $this->makeBooking(['status' => $status]);
            $payment = $this->makeApprovedPayment($booking);

            $escrow = $this->escrowService->holdEscrow($payment);

            $this->assertNotNull($escrow, "Escrow harus dibuat untuk booking berstatus {$status}.");
            $this->assertSame('pending', $escrow->status);
        }
    }

    public function test_hold_escrow_rejects_unapproved_payment(): void
    {
        $booking = $this->makeBooking();
        $payment = Pembayaran::factory()->create([
            'booking_id' => $booking->id,
            'tagihan_id' => null,
            'penghuni_id' => null,
            'amount' => $booking->expectedBookingPaymentAmount(),
            'payment_method' => 'qris',
            'verification_status' => Pembayaran::STATUS_PENDING,
        ]);

        $this->assertNull($this->escrowService->holdEscrow($payment));
        $this->assertSame(0, WalletTransaction::where('pembayaran_id', $payment->id)->count());
    }

    public function test_hold_escrow_fails_closed_when_payment_has_multiple_escrows(): void
    {
        $booking = $this->makeBooking();
        $payment = $this->makeApprovedPayment($booking);

        $this->makeEscrow($payment);
        $this->makeEscrow($payment);

        $this->assertNull($this->escrowService->holdEscrow($payment));
        $this->assertSame(2, WalletTransaction::where('pembayaran_id', $payment->id)
            ->where('type', 'escrow_in')
            ->count());
    }

    public function test_hold_escrow_rejects_cancelled_existing_escrow(): void
    {
        $booking = $this->makeBooking();
        $payment = $this->makeApprovedPayment($booking);

        $escrow = $this->makeEscrow($payment);
        WalletTransaction::whereKey($escrow->id)->update(['status' => 'cancelled']);

        $this->assertNull($this->escrowService->holdEscrow($payment));
        $this->assertSame(1, WalletTransaction::where('pembayaran_id', $payment->id)
            ->where('type', 'escrow_in')
            ->count());
        $this->assertSame('cancelled', $escrow->fresh()->status);
    }

    public function test_hold_escrow_ignores_payment_without_booking(): void
    {
        $payment = Pembayaran::factory()->create([
            'booking_id' => null,
            'tagihan_id' => null,
            'penghuni_id' => null,
            'amount' => 100000,
            'payment_method' => 'qris',
            'verification_status' => Pembayaran::STATUS_APPROVED,
        ]);

        $this->assertNull($this->escrowService->holdEscrow($payment));
        $this->assertSame(0, WalletTransaction::where('pembayaran_id', $payment->id)->count());
    }

    public function test_settle_escrow_is_idempotent(): void
    {
        $booking = $this->makeBooking();
        $this->createBookingPaymentWithEscrow($booking);

        $first = $this->escrowService->settleEscrow($booking);

        $this->assertTrue($first['ok']);
        $this->assertSame(1500000, $first['gross']);
        $this->assertSame(75000, $first['commission']);
        $this->assertSame(1425000, $first['owner_amount']);
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

        $second = $this->escrowService->settleEscrow($booking);

        $this->assertTrue($second['ok']);
        $this->assertTrue($second['idempotent'] ?? false);
        $this->assertSame(1, WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'commission')
            ->where('status', 'completed')
            ->count());
        $this->assertSame(1, WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'escrow_in')
            ->where('status', 'completed')
            ->count());
    }

    public function test_settle_escrow_fails_when_no_pending_escrow_exists(): void
    {
        $booking = $this->makeBooking();
        $this->makeApprovedPayment($booking);

        $result = $this->escrowService->settleEscrow($booking);

        $this->assertFalse($result['ok']);
        $this->assertSame('escrow_not_found', $result['error']);
        $this->assertSame(0, WalletTransaction::where('booking_id', $booking->id)->count());
    }
}
