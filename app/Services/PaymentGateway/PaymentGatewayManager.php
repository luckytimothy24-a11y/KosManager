<?php

namespace App\Services\PaymentGateway;

use InvalidArgumentException;

/**
 * Resolver driver payment gateway aktif dari config/payment-gateway.php.
 */
class PaymentGatewayManager
{
    /**
     * Resolve driver payment gateway aktif dan fail-closed terhadap secret.
     *
     * Signing key webhook bersifat mandatory. Jika kosong atau masih memakai nilai
     * placeholder (mis. "change-me-in-production"), driver tidak boleh digunakan
     * untuk membuat charge maupun memverifikasi signature.
     *
     * @throws InvalidArgumentException
     */
    public static function driver(): PaymentGatewayContract
    {
        $secret = (string) config('payment-gateway.signature_key', '');

        if ($secret === '' || in_array($secret, self::placeholderSecrets(), true)) {
            throw new InvalidArgumentException(
                'Payment gateway signature key belum dikonfigurasi dengan aman. Set PAYMENT_GATEWAY_WEBHOOK_KEY ke nilai acak panjang.'
            );
        }

        $id = config('payment-gateway.default', 'sandbox');
        $providers = config('payment-gateway.providers', []);
        $provider = $providers[$id] ?? null;

        if (! $provider || ! isset($provider['driver'])) {
            throw new InvalidArgumentException("Payment gateway driver [{$id}] tidak terdaftar.");
        }

        return app($provider['driver'], ['config' => $provider]);
    }

    public static function isValidSecret(?string $key): bool
    {
        $secret = (string) ($key ?? '');

        return $secret !== '' && ! in_array($secret, self::placeholderSecrets(), true);
    }

    private static function placeholderSecrets(): array
    {
        return array_values(array_filter(
            (array) config('payment-gateway.placeholder_secrets', ['change-me-in-production'])
        ));
    }
}
