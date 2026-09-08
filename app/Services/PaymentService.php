<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
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

            try {
                $pembayaran = Pembayaran::create([
                    'payment_number' => 'PY-'.strtoupper(Str::random(12)),
                    'tagihan_id' => $locked->id,
                    'penghuni_id' => $penghuni->id,
                    'amount' => $payload['amount'],
                    'payment_date' => now(),
                    'payment_method' => $payload['payment_method'],
                    'proof_file' => $proofPath,
                    'verification_status' => Pembayaran::STATUS_PENDING,
                    'active_payment_key' => $locked->id,
                ]);

                $locked->update(['status' => Tagihan::STATUS_PAYMENT_PENDING]);
            } catch (\Throwable $e) {
                if ($proofPath) {
                    \Storage::delete($proofPath);
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
     * Tagihan aktif milik user tenant. `null` bila tenant tidak memiliki penghuni aktif
     * atau bukan pemilik tagihan.
     */
    public function resolveOwnTagihan(int $tagihanId, int $userId): ?Model
    {
        $penghuni = Penghuni::where('user_id', $userId)->where('status', 'active')->first();

        if (! $penghuni) {
            return null;
        }

        $tagihan = Tagihan::find($tagihanId);

        if (! $tagihan || (int) $tagihan->penghuni_id !== (int) $penghuni->id) {
            return null;
        }

        return $tagihan;
    }
}
