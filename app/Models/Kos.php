<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kos extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kos';

    protected $fillable = [
        'owner_id',
        'name',
        'address',
        'description',
        'phone',
        'photo',
        'general_facilities',
        'rules',
        'payment_info',
        'status',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function kamar()
    {
        return $this->hasMany(Kamar::class);
    }

    public function admins()
    {
        return $this->belongsToMany(User::class, 'kos_user');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function penghunis()
    {
        return $this->hasMany(Penghuni::class);
    }
}
