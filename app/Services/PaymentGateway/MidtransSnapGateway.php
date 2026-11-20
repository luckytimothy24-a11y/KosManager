<?php

namespace App\Services\PaymentGateway;

use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

/**
 * Driver Midtrans Snap (sandbox/production).
 *
 * Mengimplementasi PaymentGatewayContract agar bisa di-swap dengan SandboxGateway
 * lewat config/payment-gateway.php.
 */
class MidtransSnapGateway implements PaymentGatewayContract
{
    public function __construct(protected array $config = [])
    {
        MidtransConfig::$serverKey = (string) ($this->config['server_key'] ?? config('services.payment_gateway.midtrans.server_key', ''));
        MidtransConfig::$isProduction = (bool) ($this->config['is_production'] ?? config('services.payment_gateway.midtrans.is_production', false));
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;
    }

    public function id(): string
    {
        return 'midtrans';
    }

    public function label(): string
    {
        return $this->config['payment_label'] ?? 'Midtrans Snap (VA/QRIS)';
    }

    public function paymentMethod(): string
    {
        return $this->config['payment_method'] ?? 'midtrans_snap';
    }

    public function createCharge(array $order): array
    {
        $params = [
            'transaction_details' => [
                'order_id' => $order['payment_number'],
                'gross_amount' => (int) $order['amount'],
            ],
            'customer_details' => [
                'first_name' => $order['customer_name'] ?? 'Pencari Kos',
                'email' => $order['customer_email'] ?? '',
                'phone' => $order['customer_phone'] ?? '',
            ],
            'item_details' => [
                [
                    'id' => $order['payment_number'],
                    'price' => (int) $order['amount'],
                    'quantity' => 1,
                    'name' => $order['description'] ?? 'Sewa Kos',
                ],
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        return [
            'reference' => $order['payment_number'],
            'snap_token' => $snapToken,
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/'.$snapToken,
            'instructions' => 'Bayar melalui Midtrans Snap (VA/QRIS/E-Wallet).',
        ];
    }

    public function verifySignature(array $payload, string $signature): bool
    {
        // Midtrans signature: SHA512(order_id + status_code + gross_amount + server_key)
        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $serverKey = (string) ($this->config['server_key'] ?? config('services.payment_gateway.midtrans.server_key', ''));

        if ($serverKey === '') {
            return false;
        }

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expected, $signature);
    }

    /**
     * Generate signature untuk testing (SHA512 dari order_id + status_code + gross_amount + server_key).
     */
    public function generateSignature(array $payload): string
    {
        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $serverKey = (string) ($this->config['server_key'] ?? config('services.payment_gateway.midtrans.server_key', ''));

        return hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);
    }
}
