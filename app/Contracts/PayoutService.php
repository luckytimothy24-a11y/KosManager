<?php

namespace App\Contracts;

interface PayoutService
{
    /**
     * Proses pencairan dana ke rekening owner.
     *
     * @param  array  $data  ['recipient_account', 'amount', 'reference', 'notes']
     * @return array{ok: bool, reference?: string, error?: string}
     */
    public function disburse(array $data): array;

    /**
     * Cek status pencairan.
     *
     * @return array{ok: bool, status?: string, error?: string}
     */
    public function checkStatus(string $reference): array;
}
