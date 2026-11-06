<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\OwnerBankAccount;
use App\Models\PaymentDistribution;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Distribusi pembayaran booking → Owner kos.
 *
 * Prinsip:
 * - Penerima ditentukan server-side: Booking → Kos → Owner.
 * - Tenant tidak bisa menentukan owner_id / amount / recipient.
 * - Payment approved ≠ booking approved ≠ payout processed.
 * - 1 payment → maksimal 1 distribution (unique pembayaran_id).
 * - platform_fee = 0 tanpa business rule fee existing (jangan hard-code %).
 */
class PaymentDistributionService
{
    /**
     * Catat distribution setelah payment booking BERHASIL diverifikasi.
     * Status awal: pending (belum tentu siap cair — booking masih pending).
     *
     * Idempotent: jika sudah ada distribution untuk pembayaran ini, return existing.
     *
     * @return array{ok: bool, distribution?: PaymentDistribution, error?: string}
     */
    public function recordFromApprovedPayment(Pembayaran $pembayaran): array
    {
        if (! $pembayaran->isBookingPayment() || ! $pembayaran->booking_id) {
            return ['ok' => false, 'error' => 'not_booking_payment'];
        }

        if ($pembayaran->verification_status !== Pembayaran::STATUS_APPROVED) {
            return ['ok' => false, 'error' => 'payment_not_approved'];
        }

        return (array) DB::transaction(function () use ($pembayaran) {
            $locked = Pembayaran::whereKey($pembayaran->id)->lockForUpdate()->first();

            if (! $locked || $locked->verification_status !== Pembayaran::STATUS_APPROVED) {
                return ['ok' => false, 'error' => 'payment_not_approved'];
            }

            // Idempotent: cek existing dulu
            $existing = PaymentDistribution::where('pembayaran_id', $locked->id)->first();
            if ($existing) {
                return ['ok' => true, 'distribution' => $existing];
            }

            $booking = $locked->booking;
            if (! $booking || ! $booking->kos) {
                return ['ok' => false, 'error' => 'owner_not_found'];
            }

            $ownerId = (int) $booking->kos->owner_id;
            if ($ownerId <= 0) {
                return ['ok' => false, 'error' => 'owner_not_found'];
            }

            // Nominal dasar dari server-side booking.price (bukan request client)
            $gross = round((float) ($booking->price ?? $locked->amount), 2);
            if ($gross <= 0) {
                return ['ok' => false, 'error' => 'invalid_amount'];
            }

            // Hitung platform fee dari config (PlatformFeeService)
            $platformFeeService = app(PlatformFeeService::class);
            $platformFee = $platformFeeService->calculate($gross);
            $ownerAmount = round($gross - $platformFee, 2);

            $distribution = PaymentDistribution::create([
                'pembayaran_id' => $locked->id,
                'booking_id' => $locked->booking_id,
                'owner_id' => $ownerId,
                'gross_amount' => $gross,
                'platform_fee' => $platformFee,
                'owner_amount' => $ownerAmount,
                'status' => PaymentDistribution::STATUS_PENDING,
            ]);

            return ['ok' => true, 'distribution' => $distribution];
        });
    }

    /**
     * Saat booking dikonfirmasi (approved) → distribution siap (ready).
     * Owner amount adalah hak owner setelah booking confirmed.
     *
     * @return array{ok: bool, distribution?: PaymentDistribution, error?: string}
     */
    public function markReadyForBooking(Booking $booking): array
    {
        return (array) DB::transaction(function () use ($booking) {
            $distribution = PaymentDistribution::where('booking_id', $booking->id)
                ->where('status', PaymentDistribution::STATUS_PENDING)
                ->lockForUpdate()
                ->first();

            if (! $distribution) {
                return ['ok' => false, 'error' => 'distribution_not_found'];
            }

            $distribution->update(['status' => PaymentDistribution::STATUS_READY]);

            return ['ok' => true, 'distribution' => $distribution->fresh()];
        });
    }

    /**
     * Owner menandai distribution sudah dicairkan (manual transfer).
     * BUKAN otomatis dari payment approved.
     *
     * @return array{ok: bool, distribution?: PaymentDistribution, error?: string}
     */
    public function markProcessed(PaymentDistribution $distribution, User $actor): array
    {
        return (array) DB::transaction(function () use ($distribution, $actor) {
            $locked = PaymentDistribution::whereKey($distribution->id)->lockForUpdate()->first();

            if (! $locked) {
                return ['ok' => false, 'error' => 'not_found'];
            }

            if ((int) $locked->owner_id !== (int) $actor->id && ! $actor->isSuperAdmin()) {
                return ['ok' => false, 'error' => 'forbidden'];
            }

            if ($locked->status !== PaymentDistribution::STATUS_READY) {
                return ['ok' => false, 'error' => 'not_ready'];
            }

            $locked->update([
                'status' => PaymentDistribution::STATUS_PROCESSED,
                'processed_at' => now(),
            ]);

            return ['ok' => true, 'distribution' => $locked->fresh()];
        });
    }

    /**
     * Identifikasi rekening tujuan dari relasi server-side.
     * Booking → Kos → Owner → OwnerBankAccount.
     */
    public function resolveBankAccountForBooking(Booking $booking): ?OwnerBankAccount
    {
        $ownerId = $booking->kos?->owner_id;

        if (! $ownerId) {
            return null;
        }

        return OwnerBankAccount::where('owner_id', $ownerId)->first();
    }
}
