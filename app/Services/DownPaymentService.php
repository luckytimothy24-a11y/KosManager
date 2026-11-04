<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Kontrak;

/**
 * Aturan tunggal Down Payment (DP) booking.
 *
 * - Nominal DP selalu dari config server, bukan input client.
 * - Booking legacy (down_payment_amount NULL) memakai harga booking penuh.
 * - DP hanya dipotongkan sekali pada tagihan pertama kontrak.
 * - Potongan DP di-cap sebesar subtotal tagihan; sisanya hangus (non-refundable).
 */
class DownPaymentService
{
    public function configuredAmount(): float
    {
        return round((float) config('booking.down_payment_amount', 100000), 2);
    }

    /**
     * Nominal pembayaran booking yang harus dibayar tenant.
     */
    public function bookingPaymentAmount(Booking $booking): float
    {
        return $booking->expectedBookingPaymentAmount();
    }

    /**
     * Hitung potongan DP untuk tagihan baru.
     *
     * Caller wajib berada dalam transaksi dan sudah mengunci kontrak agar
     * nilai down_payment_amount tidak dipakai dua kali secara bersamaan.
     */
    public function discountForNewBill(Kontrak $kontrak, float $subtotal): float
    {
        $remaining = round((float) ($kontrak->down_payment_amount ?? 0), 2);

        if ($remaining <= 0 || $subtotal <= 0) {
            return 0.0;
        }

        return round(min($remaining, $subtotal), 2);
    }

    /**
     * Tandai DP sudah terpakai. Sisa DP (jika subtotal lebih kecil) hangus.
     */
    public function consume(Kontrak $kontrak, float $discount): void
    {
        if ($discount > 0) {
            $kontrak->update(['down_payment_amount' => 0]);
        }
    }
}
