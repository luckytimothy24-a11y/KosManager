<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Late Fee (Denda Keterlambatan)
    |--------------------------------------------------------------------------
    |
    | Fixed penalty yang diterapkan sekali per tagihan ketika status
    | berubah dari unpaid → overdue (due_date < today, grace 0).
    | Satu sumber kebenaran untuk nominal denda.
    |
    */

    'late_fee' => [
        'fixed_amount' => (int) env('LATE_FEE_FIXED', 50000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Platform Fee (Biaya Platform)
    |--------------------------------------------------------------------------
    |
    | Biaya yang dipotong platform dari setiap distribusi pembayaran ke owner.
    | Mendukung dua mode: 'fixed' (nominal tetap) atau 'percent' (persentase).
    | Jika type='percent', fee dihitung dari gross_amount.
    | Jika type='fixed', fee menggunakan fixed_amount.
    | Default 0 = tidak ada fee platform (MVP).
    |
    */

    'platform_fee' => [
        'type' => env('PLATFORM_FEE_TYPE', 'fixed'), // 'fixed' atau 'percent'
        'fixed_amount' => (float) env('PLATFORM_FEE_FIXED', 0),
        'percent_rate' => (float) env('PLATFORM_FEE_PERCENT', 0),
    ],

];
