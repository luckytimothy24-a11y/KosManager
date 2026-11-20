<?php

namespace App\Services;

use App\Contracts\PayoutService as PayoutServiceContract;

/**
 * Implementasi manual payout: admin transfer manual, upload bukti.
 * Nanti bisa diganti ke API disbursement (Midtrans Iris, Xendit, dll).
 */
class ManualPayoutService implements PayoutServiceContract
{
    public function disburse(array $data): array
    {
        // Manual: admin melakukan transfer di luar aplikasi, lalu upload bukti.
        // Service ini hanya mencatat/mengembalikan reference.
        return [
            'ok' => true,
            'reference' => $data['reference'] ?? 'MANUAL-'.uniqid(),
            'error' => null,
        ];
    }

    public function checkStatus(string $reference): array
    {
        // Manual: status selalu "pending" sampai admin verifikasi.
        return [
            'ok' => true,
            'status' => 'pending',
            'error' => null,
        ];
    }
}
