<?php

namespace Tests;

use App\Models\Booking;
use App\Models\Pembayaran;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * H-01 test fixture: payment approved sesuai nominal server + tepat satu
     * escrow pending, sehingga check-in/settlement dapat diuji.
     */
    protected function createBookingPaymentWithEscrow(Booking $booking, ?int $ownerId = null): Pembayaran
    {
        $ownerId ??= (int) $booking->kos->owner_id;
        $amount = $booking->expectedBookingPaymentAmount();

        $payment = Pembayaran::factory()->create([
            'booking_id' => $booking->id,
            'tagihan_id' => null,
            'penghuni_id' => null,
            'amount' => $amount,
            'payment_date' => now(),
            'payment_method' => 'qris',
            'verification_status' => Pembayaran::STATUS_APPROVED,
            'verified_at' => now(),
        ]);

        WalletTransaction::create([
            'owner_id' => $ownerId,
            'booking_id' => $booking->id,
            'pembayaran_id' => $payment->id,
            'type' => 'escrow_in',
            'amount' => (int) $amount,
            'description' => "Escrow test booking {$booking->booking_code}",
            'status' => 'pending',
            'metadata' => [
                'gross_amount' => (int) $amount,
                'commission_rate' => 5,
            ],
        ]);

        return $payment;
    }

    protected function setUp(): void
    {
        parent::setUp();

        app('cache')->flush();
    }
}
