<?php

namespace App\Services;

/**
 * PlatformFeeService
 *
 * Service untuk menghitung biaya platform yang dipotong dari setiap distribusi pembayaran.
 * Mendukung dua mode: fixed amount atau percentage.
 *
 * @author KosManager Team
 */
class PlatformFeeService
{
    /**
     * Hitung platform fee dari gross amount
     *
     * @param  float  $grossAmount  Nilai bruto sebelum dipotong fee
     * @return float Platform fee (selalu >= 0)
     */
    public function calculate(float $grossAmount): float
    {
        $config = config('kosmanager.platform_fee', []);
        $type = $config['type'] ?? 'fixed';

        if ($type === 'percent') {
            $rate = (float) ($config['percent_rate'] ?? 0);

            return round($grossAmount * ($rate / 100), 2);
        }

        // Default: fixed
        return round((float) ($config['fixed_amount'] ?? 0), 2);
    }

    /**
     * Hitung platform fee dari net amount (hasil koreksi balik)
     *
     * Digunakan ketika sudah ada net amount dan perlu tahu berapa fee yang dikurangi.
     * Untuk mode percent: fee = gross * rate / 100, lalu gross = fee + net
     * Untuk mode fixed: fee = fixed_amount (sama dengan calculate)
     *
     * @param  float  $netAmount  Nilai neto setelah dipotong fee
     * @return float Platform fee
     */
    public function calculateForNet(float $netAmount): float
    {
        $config = config('kosmanager.platform_fee', []);
        $type = $config['type'] ?? 'fixed';

        if ($type === 'percent') {
            $rate = (float) ($config['percent_rate'] ?? 0);
            // net = gross - (gross * rate / 100)
            // net = gross * (1 - rate / 100)
            // gross = net / (1 - rate / 100)
            if ($rate >= 100) {
                return 0; // Jika rate >= 100%, tidak valid
            }
            $factor = 1 - ($rate / 100);
            $gross = $factor > 0 ? $netAmount / $factor : 0;

            return round($gross - $netAmount, 2);
        }

        // Default: fixed
        return round((float) ($config['fixed_amount'] ?? 0), 2);
    }

    /**
     * Hitung net amount setelah dipotong platform fee
     *
     * @param  float  $grossAmount  Nilai bruto
     * @return float Nilai neto setelah fee
     */
    public function getNet(float $grossAmount): float
    {
        return round($grossAmount - $this->calculate($grossAmount), 2);
    }

    /**
     * Get konfigurasi platform fee saat ini
     */
    public function getConfig(): array
    {
        return config('kosmanager.platform_fee', [
            'type' => 'fixed',
            'fixed_amount' => 0,
            'percent_rate' => 0,
        ]);
    }
}
