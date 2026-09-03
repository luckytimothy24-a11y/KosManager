<?php

use App\Services\PaymentGateway\SandboxGateway;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway Driver
    |--------------------------------------------------------------------------
    |
    | Driver aktif untuk pembayaran online otomatis. Default adalah
    | "sandbox": simulasi gateway tanpa koneksi eksternal agar seluruh
    | alur (buat charge, signature webhook, auto-verifikasi) dapat diuji
    | end-to-end. Ganti ke driver provider nyata dengan mengimplementasi
    | App\Services\PaymentGateway\PaymentGatewayContract dan menambah
    | entri pada "providers" di bawah.
    |
    */

    'default' => env('PAYMENT_GATEWAY', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Signature Key Webhook
    |--------------------------------------------------------------------------
    |
    | Kunci HMAC (sha256) yang dipakai driver untuk memverifikasi callback
    | gateway. Bersifat MANDATORY: tidak ada nilai default yang aman.
    |
    | Jika env PAYMENT_GATEWAY_WEBHOOK_KEY kosong/tidak di-set, seluruh operasi
    | gateway akan fail-closed (lihat PaymentGatewayManager). Jangan pernah memakai
    | nilai placeholder di produksi.
    |
    */

    'signature_key' => env('PAYMENT_GATEWAY_WEBHOOK_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Placeholder / nilai legacy yang TIDAK boleh dipakai sebagai secret
    |--------------------------------------------------------------------------
    |
    | Daftar nilai yang dianggap tidak aman. Bila secret ter-resolusi ke salah satu
    | nilai ini, PaymentGatewayManager akan fail-closed.
    |
    */

    'placeholder_secrets' => ['change-me-in-production'],

    /*
    |--------------------------------------------------------------------------
    | Availiable Gateway Drivers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'sandbox' => [
            'driver' => SandboxGateway::class,
            'payment_method' => 'e_wallet',
            'payment_label' => 'QRIS / Virtual Account (Simulasi)',
            'reference_prefix' => 'VA-',
            'lifetime_hours' => 24,
        ],
    ],

];
