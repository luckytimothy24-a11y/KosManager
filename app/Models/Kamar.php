<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kamar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kamar';

    protected $fillable = [
        'kos_id',
        'room_number',
        'room_name',
        'floor',
        'room_type',
        'daily_price',
        'monthly_price',
        'area',
        'description',
        'photo',
        'status',
    ];

    protected $casts = [
        'daily_price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
    ];

    public function kos()
    {
        return $this->belongsTo(Kos::class);
    }

    public function fasilitas()
    {
        return $this->belongsToMany(Fasilitas::class, 'kamar_fasilitas');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function kontraks()
    {
        return $this->hasMany(Kontrak::class);
    }

    public function penghunis()
    {
        return $this->hasMany(Penghuni::class);
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
}
