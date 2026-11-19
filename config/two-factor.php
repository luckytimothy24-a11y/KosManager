<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enforcement
    |--------------------------------------------------------------------------
    |
    | Jika true, role di bawah ini wajib mengaktifkan 2FA sebelum dapat
    | mengakses area admin. Untuk demo/presentasi biarkan false agar tidak
    | mengunci akun yang belum menyiapkan authenticator.
    |
    */

    'enforce' => env('TWO_FACTOR_ENFORCE', false),

    'enforce_roles' => ['super_admin', 'admin'],

    /*
    |--------------------------------------------------------------------------
    | Session verification lifetime (menit)
    |--------------------------------------------------------------------------
    |
    | Berapa lama verifikasi 2FA berlaku sebelum user diminta kode lagi.
    | Default 12 jam (720 menit).
    |
    */

    'session_minutes' => (int) env('TWO_FACTOR_SESSION_MINUTES', 720),

    /*
    |--------------------------------------------------------------------------
    | Recovery codes
    |--------------------------------------------------------------------------
    */

    'recovery_code_count' => 8,

];
