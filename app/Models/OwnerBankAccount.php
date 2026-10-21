<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Rekening bank Owner penerima pembayaran booking.
 * account_number disimpan sebagai STRING (leading zero aman).
 */
class OwnerBankAccount extends Model
{
    protected $fillable = [
        'owner_id',
        'bank_name',
        'account_number',
        'account_holder_name',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Masking: ****7890 — jangan tampilkan nomor lengkap di UI umum.
     */
    public function maskedAccountNumber(): string
    {
        $num = (string) $this->account_number;
        if ($num === '') {
            return '-';
        }

        $len = mb_strlen($num);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        return str_repeat('*', $len - 4).mb_substr($num, -4);
    }
}
