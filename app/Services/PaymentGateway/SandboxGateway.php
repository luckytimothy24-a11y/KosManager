<?php

namespace App\Services\PaymentGateway;

use Illuminate\Support\Str;

/**
 * Driver simulasi payment gateway (tanpa koneksi eksternal).
 *
 * Digunakan sebagai default agar seluruh alur pembayaran online dapat
 * diuji end-to-end dan berfungsi tanpa kredensial provider. Signature
 * webhook dibuat dari HMAC-SHA256 payload + config signature_key.
 */
class SandboxGateway implements PaymentGatewayContract
{
    public function __construct(protected array $config = []) {}

    public function id(): string
    {
        return 'sandbox';
    }

    public function label(): string
    {
        return $this->config['payment_label'] ?? 'QRIS / Virtual Account (Simulasi)';
    }

    public function paymentMethod(): string
    {
        return $this->config['payment_method'] ?? 'e_wallet';
    }

    public function createCharge(array $order): array
    {
        $prefix = $this->config['reference_prefix'] ?? 'VA-';
        $reference = $prefix.strtoupper(Str::random(12));

        return [
            'reference' => $reference,
            'instructions' => 'Scan QRIS atau bayar lewat virtual account dengan kode '.$reference
                .' sebesar Rp '.number_format((float) $order['amount'], 0, ',', '.').'.'
                .' Pembayaran diverifikasi otomatis oleh sistem.',
        ];
    }

    public function verifySignature(array $payload, string $signature): bool
    {
        $secret = config('payment-gateway.signature_key', '');

        if (! PaymentGatewayManager::isValidSecret($secret)) {
            return false;
        }

        $encoded = json_encode($payload);
        if ($encoded === false) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $encoded, $secret), $signature);
    }
}
