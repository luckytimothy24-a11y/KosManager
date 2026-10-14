<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Buat pembayaran manual (tunai) dengan business rule existing:
     * - transaction + lockForUpdate pada tagihan untuk cegah race condition,
     * - cek status tagihan PAYABLE (unpaid/overdue),
     * - cek nominal pembayaran harus sesuai total tagihan,
     * - cek tidak ada pembayaran aktif (pending/approved) untuk tagihan yang sama,
     * - simpan bukti (opsional) ke disk privat (storage/app/bukti-pembayaran),
     * - set status tagihan ke pending_verification.
     *
     * @param  array<string, mixed>  $payload  [penghuni, tagihan, amount, payment_method, proof_file(optional)]
     * @return array{ok: bool, error?: string, message?: string, pembayaran?: Pembayaran, tagihan?: Tagihan}
     */
    public function createManual(array $payload): array
    {
        $penghuni = $payload['penghuni'];
        $tagihan = $payload['tagihan'];

        $saved = \DB::transaction(function () use ($payload, $penghuni, $tagihan) {
            $locked = Tagihan::whereKey($tagihan->id)->lockForUpdate()->first();

            if (! $locked || ! in_array($locked->status, Tagihan::PAYABLE)) {
                return 'terminal';
            }

            $hasActivePayment = Pembayaran::where('tagihan_id', $locked->id)
                ->whereIn('verification_status', Pembayaran::ACTIVE_VERIFICATIONS)
                ->exists();

            if ($hasActivePayment) {
                return 'duplicate';
            }

            $proofPath = $payload['proof_file'] ?? null;

            $rentAmount = null;
            $penaltyAmount = null;
            if ($locked->penalty !== null) {
                $penaltyAmount = round((float) $locked->penalty, 2);
                $rentAmount = round((float) $payload['amount'] - $penaltyAmount, 2);
                if ($rentAmount < 0) {
                    $rentAmount = round((float) $payload['amount'], 2);
                    $penaltyAmount = 0;
                }
            }

            try {
                $pembayaran = Pembayaran::create([
                    'payment_number' => 'PY-'.strtoupper(Str::random(12)),
                    'tagihan_id' => $locked->id,
                    'penghuni_id' => $penghuni->id,
                    'amount' => $payload['amount'],
                    'rent_amount' => $rentAmount,
                    'penalty_amount' => $penaltyAmount,
                    'payment_date' => now(),
                    'payment_method' => $payload['payment_method'],
                    'proof_file' => $proofPath,
                    'verification_status' => Pembayaran::STATUS_PENDING,
                    'active_payment_key' => $locked->id,
                    'notes' => $payload['notes'] ?? null,
                ]);

                $locked->update(['status' => Tagihan::STATUS_PAYMENT_PENDING]);
            } catch (\Throwable $e) {
                if ($proofPath) {
                    \Storage::disk('private')->delete($proofPath);
                }

                throw $e;
            }

            return [$locked, $pembayaran];
        });

        if ($saved === 'terminal') {
            return [
                'ok' => false,
                'error' => 'terminal',
                'message' => 'Tagihan ini tidak dapat dibayar (sudah dibayar atau menunggu verifikasi).',
            ];
        }

        if ($saved === 'duplicate') {
            return [
                'ok' => false,
                'error' => 'duplicate',
                'message' => 'Tagihan ini tidak dapat dibayar (sudah dibayar atau menunggu verifikasi).',
            ];
        }

        if (! is_array($saved)) {
            return [
                'ok' => false,
                'error' => 'terminal',
                'message' => 'Tagihan ini tidak dapat dibayar (sudah dibayar atau menunggu verifikasi).',
            ];
        }

        return [
            'ok' => true,
            'tagihan' => $saved[0],
            'pembayaran' => $saved[1],
        ];
    }

    /**
     * Buat pembayaran booking (prerequisite konfirmasi booking).
     *
     * - amount SELALU dari booking.price (server-side) — request client diabaikan.
     * - verification_status SELALU pending (tenant tidak bisa approve sendiri).
     * - booking.status TIDAK berubah.
     * - hanya pemilik booking (tenant) yang boleh submit.
     * - satu payment aktif per booking.
     *
     * @return array{ok: bool, error?: string, message?: string, pembayaran?: Pembayaran}
     */
    public function createForBooking(Booking $booking, User $tenant, string $paymentMethod, ?string $proofPath = null, ?string $notes = null): array
    {
        if ((int) $booking->user_id !== (int) $tenant->id) {
            return [
                'ok' => false,
                'error' => 'forbidden',
                'message' => 'Anda tidak memiliki akses untuk booking ini.',
            ];
        }

        if ($booking->status !== Booking::STATUS_PENDING) {
            return [
                'ok' => false,
                'error' => 'not_pending',
                'message' => 'Pembayaran booking hanya untuk booking berstatus pending.',
            ];
        }

        if (! in_array($paymentMethod, ['cash', 'transfer_bank'], true)) {
            return [
                'ok' => false,
                'error' => 'invalid_method',
                'message' => 'Metode pembayaran tidak valid.',
            ];
        }

        if ($paymentMethod === 'transfer_bank' && ! $proofPath) {
            return [
                'ok' => false,
                'error' => 'proof_required',
                'message' => 'Bukti pembayaran wajib diunggah.',
            ];
        }

        $saved = \DB::transaction(function () use ($booking, $tenant, $paymentMethod, $proofPath, $notes) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->first();

            if (! $locked || $locked->status !== Booking::STATUS_PENDING) {
                return 'invalid_state';
            }

            if ((int) $locked->user_id !== (int) $tenant->id) {
                return 'forbidden';
            }

            $hasActive = Pembayaran::where('booking_id', $locked->id)
                ->whereIn('verification_status', Pembayaran::ACTIVE_VERIFICATIONS)
                ->exists();

            if ($hasActive) {
                return 'duplicate';
            }

            // NOMINAL DARI SERVER — bukan request client.
            $amount = (float) $locked->price;

            try {
                $pembayaran = Pembayaran::create([
                    'payment_number' => 'PY-'.strtoupper(Str::random(12)),
                    'booking_id' => $locked->id,
                    'tagihan_id' => null,
                    'penghuni_id' => null,
                    'amount' => $amount,
                    'payment_date' => now(),
                    'payment_method' => $paymentMethod,
                    'proof_file' => $proofPath,
                    'verification_status' => Pembayaran::STATUS_PENDING,
                    'notes' => $notes,
                ]);
            } catch (\Throwable $e) {
                if ($proofPath) {
                    \Storage::disk('private')->delete($proofPath);
                }

                throw $e;
            }

            return $pembayaran;
        });

        if ($saved === 'invalid_state') {
            return [
                'ok' => false,
                'error' => 'not_pending',
                'message' => 'Pembayaran booking hanya untuk booking berstatus pending.',
            ];
        }

        if ($saved === 'forbidden') {
            return [
                'ok' => false,
                'error' => 'forbidden',
                'message' => 'Anda tidak memiliki akses untuk booking ini.',
            ];
        }

        if ($saved === 'duplicate') {
            return [
                'ok' => false,
                'error' => 'duplicate',
                'message' => 'Pembayaran untuk booking ini sudah dikirim dan menunggu verifikasi.',
            ];
        }

        return [
            'ok' => true,
            'pembayaran' => $saved,
        ];
    }

    /**
     * Tagihan aktif milik user tenant. `null` bila tenant tidak memiliki penghuni aktif
     * atau bukan pemilik tagihan.
     */
    public function resolveOwnTagihan(int $tagihanId, int $userId): ?Model
    {
        $tagihan = Tagihan::find($tagihanId);

        if (! $tagihan) {
            return null;
        }

        $hasOwnership = Penghuni::where('user_id', $userId)
            ->where('status', 'active')
            ->where('id', $tagihan->penghuni_id)
            ->exists();

        return $hasOwnership ? $tagihan : null;
    }
}
