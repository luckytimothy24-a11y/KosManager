<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Pembayaran;
use App\Models\User;
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
        if (! $pembayaran->booking_id || ! $pembayaran->booking) {
            return null;
        }

        $booking = $pembayaran->booking;
        $ownerId = (int) ($booking->kos->owner_id ?? 0);

        if ($ownerId <= 0) {
            return null;
        }

        return DB::transaction(function () use ($pembayaran, $booking, $ownerId) {
            // Lock booking first to serialize escrow creation with settlement
            $lockedBooking = Booking::whereKey($booking->id)->lockForUpdate()->first();

            if (! $lockedBooking) {
                return null;
            }

            // Validasi ulang booking setelah lock
            if (! in_array($lockedBooking->status, [Booking::STATUS_APPROVED], true)) {
                return null;
            }

            // Idempotent: cek existing
            $existing = WalletTransaction::where('pembayaran_id', $pembayaran->id)
                ->where('type', 'escrow_in')
                ->first();

            if ($existing) {
                return $existing;
            }

            // Validasi kesesuaian payment dengan booking terkunci
            if ($lockedBooking->id !== $booking->id) {
                return null;
            }

            return WalletTransaction::create([
                'owner_id' => $ownerId,
                'booking_id' => $booking->id,
                'pembayaran_id' => $pembayaran->id,
                'type' => 'escrow_in',
                'amount' => (int) $pembayaran->amount,
                'description' => "Escrow pembayaran booking {$booking->booking_code}",
                'reference_number' => $pembayaran->gateway_reference,
                'status' => 'pending',
                'metadata' => [
                    'gross_amount' => (int) $pembayaran->amount,
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

            // 2. Catat owner entitlement setelah komisi.
            WalletTransaction::create([
                'owner_id' => $ownerId,
                'booking_id' => $lockedBooking->id,
                'pembayaran_id' => $escrow->pembayaran_id,
                'type' => 'escrow_in',
                'amount' => $ownerAmount,
                'description' => "Settlement escrow booking {$lockedBooking->booking_code} (setelah komisi)",
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

    /**
     * Proses refund: batalkan escrow pending dan catat refund entry.
     */
    public function processRefund(Booking $booking, int $refundAmount): void
    {
        $ownerId = (int) ($booking->kos->owner_id ?? 0);

        if ($ownerId <= 0) {
            return;
        }

        DB::transaction(function () use ($booking, $ownerId, $refundAmount) {
            // Batalkan escrow pending
            WalletTransaction::where('booking_id', $booking->id)
                ->where('type', 'escrow_in')
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            // Catat refund entry (negatif)
            WalletTransaction::create([
                'owner_id' => $ownerId,
                'booking_id' => $booking->id,
                'type' => 'refund',
                'amount' => -$refundAmount,
                'description' => "Refund booking {$booking->booking_code}",
                'status' => 'completed',
                'metadata' => [
                    'refund_amount' => $refundAmount,
                ],
            ]);
        });
    }
}
