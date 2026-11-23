<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Canonical roles (DB values)
    |--------------------------------------------------------------------------
    | `users.role` menyimpan string ini. Jangan gunakan nilai lain di luar
    | daftar ini untuk data baru.
    */
    'canonical' => [
        'super_admin',
        'admin',
        'owner',
        'pencari',
        'technician',
    ],

    /*
    |--------------------------------------------------------------------------
    | Internal roles (tidak boleh dibuat/di-assign oleh admin biasa)
    |--------------------------------------------------------------------------
    */
    'internal' => [
        'super_admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy -> canonical mapping
    |--------------------------------------------------------------------------
    | Dipakai untuk kompatibilitas baca data lama dan migrasi bertahap.
    | `pencari` tetap canonical; `tenant` hanya alias aplikasi.
    */
    'legacy_map' => [
        'tenant' => 'pencari',
        'technician' => 'technician',
    ],

    /*
    |--------------------------------------------------------------------------
    | Backward-compatible aliases (aplikasi, bukan DB)
    |--------------------------------------------------------------------------
    */
    'aliases' => [
        'tenant' => 'pencari',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role demo (HANYA local/testing, tidak boleh production)
    |--------------------------------------------------------------------------
    */
    'demo' => [
        'technician' => [
            'name' => 'Teknisi Demo',
            'email' => 'teknisi@kosmanager.test',
            'password' => 'password',
        ],
    ],
];
