<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Rekening tujuan payout milik Pemilik Kos Asli.
 *
 * TERPISAH dari OwnerBankAccount (rekening Owner/pengelola — tujuan
 * pembayaran tenant). account_number string — leading zero aman.
 */
class PropertyOwnerBankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_owner_id',
        'bank_name',
        'account_number',
        'account_holder_name',
    ];

    public function propertyOwner()
    {
        return $this->belongsTo(PropertyOwner::class);
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
