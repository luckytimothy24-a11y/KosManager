<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Down Payment (DP) Booking
    |--------------------------------------------------------------------------
    |
    | Nominal DP bersifat tetap dan ditentukan server-side. Client tidak boleh
    | mengirim/mengubah nominal ini. DP dibayarkan saat booking, diverifikasi
    | owner, lalu dipotongkan pada tagihan sewa pertama.
    |
    */
    'down_payment_amount' => 100000,
];
