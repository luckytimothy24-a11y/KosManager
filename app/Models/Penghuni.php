<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penghuni extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'kos_id',
        'kamar_id',
        'identity_number',
        'phone',
        'address',
        'check_in_date',
        'status',
    ];

    protected $casts = [
        'check_in_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kos()
    {
        return $this->belongsTo(Kos::class);
    }

    public function kamar()
    {
        return $this->belongsTo(Kamar::class);
    }

    public function kontraks()
    {
        return $this->hasMany(Kontrak::class);
    }

    public function checkIns()
    {
        return $this->hasMany(CheckIn::class);
    }

    public function checkOuts()
    {
        return $this->hasMany(CheckOut::class);
    }

    public function tagihans()
    {
        return $this->hasMany(Tagihan::class);
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }
}
