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

    /*
    |--------------------------------------------------------------------------
    | Kebijakan Refund DP (TODO: definisikan kebijakan resmi)
    |--------------------------------------------------------------------------
    |
    | TODO: Kebijakan refund DP belum ditetapkan secara resmi.
    | Saat ini yang tercatat di kode:
    | - DP bersifat non-refundable jika booking ditolak atau dibatalkan.
    | - DP dipotongkan dari tagihan sewa pertama setelah check-in.
    | Isi kebijakan resmi di sini setelah disepakati.
    |
    */
    'dp_refund_policy' => 'Down Payment (DP) bersifat non-refundable. Jika booking ditolak pemilik kos atau dibatalkan, DP tidak akan dikembalikan. DP akan dipotongkan dari tagihan sewa pertama setelah check-in berhasil. TODO: definisikan kebijakan resmi refund DP.',
];
