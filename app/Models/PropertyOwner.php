<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Pemilik Kos Asli (property owner) — pihak yang benar-benar memiliki
 * properti dan berhak menerima hasil pendapatan (payout/settlement).
 *
 * BERBEDA dari role `owner` (pengelola/partner/perantara di platform).
 * Entity ringan, bukan user platform.
 */
class PropertyOwner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
    ];

    public function bankAccount()
    {
        return $this->hasOne(PropertyOwnerBankAccount::class);
    }

    public function kos()
    {
        return $this->hasMany(Kos::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }
}
