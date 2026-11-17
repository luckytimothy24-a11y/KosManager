<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Pembayaran;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class EscrowService
{
    /**
     * Buat entri escrow (dana ditahan) saat pembayaran diverifikasi.
     * Dana BELUM masuk saldo owner — status 'pending' sampai booking selesai.
     */
    public function holdEscrow(Pembayaran $pembayaran): ?WalletTransaction
    {
        if (! $pembayaran->booking_id) {
            return null;
        }

        return DB::transaction(function () use ($pembayaran) {
            // Lock booking lebih dulu agar escrow creation serial dengan settlement.
            $lockedBooking = Booking::whereKey($pembayaran->booking_id)->lockForUpdate()->first();

            if (! $lockedBooking) {
                return null;
            }

            // Escrow hanya untuk booking yang masih berjalan.
            if (in_array($lockedBooking->status, [
                Booking::STATUS_REJECTED,
                Booking::STATUS_CANCELLED,
                Booking::STATUS_EXPIRED,
                Booking::STATUS_COMPLETED,
            ], true)) {
                return null;
            }

            // Re-read payment tanpa lock tambahan. Caller verification sudah
            // memegang payment lock; lock payment di sini akan membalik lock order.
            $payment = Pembayaran::whereKey($pembayaran->id)->first();

            if (! $payment || $payment->verification_status !== Pembayaran::STATUS_APPROVED) {
                return null;
            }

            if ((int) $payment->booking_id !== (int) $lockedBooking->id) {
                return null;
            }

            $expectedAmount = $lockedBooking->expectedBookingPaymentAmount();
            if ($expectedAmount <= 0
                || round((float) $payment->amount, 2) !== round($expectedAmount, 2)) {
                return null;
            }

            $ownerId = (int) ($lockedBooking->kos->owner_id ?? 0);
            if ($ownerId <= 0) {
                return null;
            }

            $existing = WalletTransaction::where('pembayaran_id', $payment->id)
                ->where('type', 'escrow_in')
                ->lockForUpdate()
                ->get();

            // Ambigu: lebih dari satu escrow untuk satu payment.
            // Jangan repair otomatis dan jangan membuat escrow baru.
            if ($existing->count() > 1) {
                return null;
            }

            if ($existing->count() === 1) {
                $escrow = $existing->first();

                if ((int) $escrow->booking_id !== (int) $lockedBooking->id
                    || round((float) $escrow->amount, 2) !== round((float) $payment->amount, 2)
                    || $escrow->status !== 'pending') {
                    return null;
                }

                return $escrow;
            }

            return WalletTransaction::create([
                'owner_id' => $ownerId,
                'booking_id' => $lockedBooking->id,
                'pembayaran_id' => $payment->id,
                'type' => 'escrow_in',
                'amount' => (int) $payment->amount,
                'description' => "Escrow pembayaran booking {$lockedBooking->booking_code}",
                'reference_number' => $payment->gateway_reference,
                'status' => 'pending',
                'metadata' => [
                    'gross_amount' => (int) $payment->amount,
                    'commission_rate' => (int) (DB::table('settings')->where('key', 'commission_rate')->value('value') ?? 5),
                ],
            ]);
        });
    }

    /**
     * Saat booking SELESAI: hitung komisi, lalu tambahkan sisa ke saldo owner.
     * Buat 2 entri ledger: commission (negatif) dan completed escrow.
     */
    public function settleEscrow(Booking $booking): array
    {
        $ownerId = (int) ($booking->kos->owner_id ?? 0);

        if ($ownerId <= 0) {
            return ['ok' => false, 'error' => 'owner_not_found'];
        }

        return DB::transaction(function () use ($booking, $ownerId) {
            // Lock the booking first so concurrent settlement attempts for the
            // same booking are serialized before any financial row is created.
            $lockedBooking = Booking::whereKey($booking->id)->lockForUpdate()->first();

            if (! $lockedBooking) {
                return ['ok' => false, 'error' => 'booking_not_found'];
            }

            if (! in_array($lockedBooking->status, [Booking::STATUS_APPROVED, Booking::STATUS_COMPLETED], true)) {
                return ['ok' => false, 'error' => 'invalid_booking_status'];
            }

            // Cari escrow pending untuk booking ini.
            $escrow = WalletTransaction::where('booking_id', $lockedBooking->id)
                ->where('type', 'escrow_in')
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            // If no pending escrow remains, an already completed settlement marker
            // makes this call idempotent.
            if (! $escrow) {
                $existingMarker = WalletTransaction::where('booking_id', $lockedBooking->id)
                    ->whereNotNull('metadata')
                    ->get()
                    ->first(function (WalletTransaction $transaction) {
                        $metadata = is_array($transaction->metadata) ? $transaction->metadata : [];

                        return isset($metadata['settled_from_escrow_id'], $metadata['settlement_entry_role'])
                            && in_array($metadata['settlement_entry_role'], ['commission', 'owner_entitlement'], true);
                    });

                if ($existingMarker) {
                    return ['ok' => true, 'idempotent' => true];
                }

                return ['ok' => false, 'error' => 'escrow_not_found'];
            }

            $sourceEscrowId = (int) $escrow->id;

            // Check for a completed settlement marker before creating any new row.
            $markers = WalletTransaction::where('booking_id', $lockedBooking->id)
                ->whereNotNull('metadata')
                ->get()
                ->filter(function (WalletTransaction $transaction) use ($sourceEscrowId) {
                    $metadata = is_array($transaction->metadata) ? $transaction->metadata : [];

                    return (int) ($metadata['settled_from_escrow_id'] ?? 0) === $sourceEscrowId
                        && in_array($metadata['settlement_entry_role'] ?? null, ['commission', 'owner_entitlement'], true);
                });

            $commissionMarker = $markers->first(function (WalletTransaction $transaction) {
                $metadata = is_array($transaction->metadata) ? $transaction->metadata : [];

                return ($metadata['settlement_entry_role'] ?? null) === 'commission'
                    && $transaction->status === 'completed';
            });

            $ownerMarker = $markers->first(function (WalletTransaction $transaction) {
                $metadata = is_array($transaction->metadata) ? $transaction->metadata : [];

                return ($metadata['settlement_entry_role'] ?? null) === 'owner_entitlement'
                    && $transaction->type === 'escrow_in'
                    && $transaction->status === 'completed';
            });

            if ($commissionMarker && $ownerMarker) {
                return ['ok' => true, 'idempotent' => true];
            }

            if ($markers->isNotEmpty()) {
                return ['ok' => false, 'error' => 'inconsistent_settlement'];
            }

            // Legacy rows without a marker make automatic settlement unsafe.
            $unmarkedFinancialEntries = WalletTransaction::where('booking_id', $lockedBooking->id)
                ->whereIn('type', ['commission', 'escrow_in'])
                ->where('status', 'completed')
                ->where(function ($query) {
                    $query->whereNull('metadata')
                        ->orWhereRaw("JSON_EXTRACT(metadata, '$.settled_from_escrow_id') IS NULL");
                })
                ->exists();

            if ($unmarkedFinancialEntries) {
                return ['ok' => false, 'error' => 'inconsistent_legacy_settlement'];
            }

            $grossAmount = (int) $escrow->amount;
            $commissionRate = (int) (DB::table('settings')->where('key', 'commission_rate')->value('value') ?? 5);
            $commission = (int) round($grossAmount * $commissionRate / 100);
            $ownerAmount = $grossAmount - $commission;

            // 1. Catat komisi platform (negatif dari perspektif owner)
            WalletTransaction::create([
                'owner_id' => $ownerId,
                'booking_id' => $lockedBooking->id,
                'pembayaran_id' => $escrow->pembayaran_id,
                'type' => 'commission',
                'amount' => -$commission,
                'description' => "Komisi platform {$commissionRate}% dari booking {$lockedBooking->booking_code}",
                'reference_number' => $escrow->reference_number,
                'status' => 'completed',
                'metadata' => [
                    'gross_amount' => $grossAmount,
                    'commission_rate' => $commissionRate,
                    'commission_amount' => $commission,
                    'settled_from_escrow_id' => $sourceEscrowId,
                    'settlement_entry_role' => 'commission',
                ],
            ]);

            // 2. Catat owner entitlement GROSS. Komisi di atas menjadi pengurang
            //    saldo, sehingga getBalance() = gross - komisi = hak bersih owner.
            //    Jangan mencatat amount net di sini karena komisi akan terhitung dua kali.
            WalletTransaction::create([
                'owner_id' => $ownerId,
                'booking_id' => $lockedBooking->id,
                'pembayaran_id' => $escrow->pembayaran_id,
                'type' => 'escrow_in',
                'amount' => $grossAmount,
                'description' => "Settlement escrow booking {$lockedBooking->booking_code} (gross)",
                'reference_number' => $escrow->reference_number,
                'status' => 'completed',
                'metadata' => [
                    'gross_amount' => $grossAmount,
                    'commission' => $commission,
                    'owner_amount' => $ownerAmount,
                    'settled_from_escrow_id' => $sourceEscrowId,
                    'settlement_entry_role' => 'owner_entitlement',
                ],
            ]);

            return [
                'ok' => true,
                'gross' => $grossAmount,
                'commission' => $commission,
                'owner_amount' => $ownerAmount,
            ];
        });
    }

    /**
     * Hitung saldo owner dari ledger (sum of completed transactions).
     */
    public function getBalance(int $ownerId): int
    {
        return (int) WalletTransaction::where('owner_id', $ownerId)
            ->where('status', 'completed')
            ->sum('amount');
    }
}
