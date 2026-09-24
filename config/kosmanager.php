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

];
