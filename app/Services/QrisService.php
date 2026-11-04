<?php

namespace App\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QrisService
{
    /**
     * Generate QRIS QR code dari data pembayaran
     * Format QRIS: staticnya atau dynamic dengan ref pembayaran
     */
    public static function generateQrCode(
        string $reference = null,
        float $amount = null,
        int $size = 300
    ): string {
        // QRIS static string (bisa diganti dengan dynamic merchant code dari gateway)
        $qrisString = self::buildQrisString($reference, $amount);

        $qrCode = QrCode::create($qrisString)
            ->setSize($size)
            ->setMargin(10);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Return base64 encoded image untuk ditampilkan di browser
        return 'data:image/png;base64,' . base64_encode($result->getString());
    }

    /**
     * Build QRIS string
     * Format minimal: static merchant QRIS atau dynamic dengan referensi pembayaran
     */
    private static function buildQrisString(string $reference = null, float $amount = null): string
    {
        // Opsi 1: Static QRIS merchant code
        $merchantCode = env('QRIS_MERCHANT_CODE', '000201011230');

        // Opsi 2: Dynamic dengan referensi pembayaran (jika tersedia)
        if ($reference) {
            // Format: QRIS_TYPE|MERCHANT|AMOUNT|REFERENCE
            // Ini contoh, sesuaikan dengan kebutuhan gateway Anda
            return "{$merchantCode}|{$reference}";
        }

        return $merchantCode;
    }

    /**
     * Format nominal untuk ditampilkan di QRIS label
     */
    public static function formatAmount(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
