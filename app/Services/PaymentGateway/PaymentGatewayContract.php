<?php

namespace App\Services\PaymentGateway;

/**
 * Kontrak driver payment gateway bersifat pluggable.
 *
 * Driver default adalah SandboxGateway (tanpa koneksi eksternal) sehingga
 * seluruh alur dapat diuji. Driver provider nyata cukup mengimplementasi
 * kontrak ini dan diregistrasi pada config/payment-gateway.php.
 */
interface PaymentGatewayContract
{
    /**
     * Nama unik driver (contoh: sandbox, midtrans, xendit).
     */
    public function id(): string;

    /**
     * Nama tampilan driver untuk UI (bahasa Indonesia).
     */
    public function label(): string;

    /**
     * Metode pembayaran (PaymentLabels.paymentMethod) yang dipakai driver.
     */
    public function paymentMethod(): string;

    /**
     * Buat transaksi pembayaran baru di gateway.
     *
     * @param  array<string, mixed>  $order  ['payment_number', 'amount', 'description']
     * @return array<string, mixed> ['reference', 'instructions', ...]
     */
    public function createCharge(array $order): array;

    /**
     * Validasi signature callback webhook dari gateway.
     *
     * @param  array<string, mixed>  $payload
     */
    public function verifySignature(array $payload, string $signature): bool;
}
