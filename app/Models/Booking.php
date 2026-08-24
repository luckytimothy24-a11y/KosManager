<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_code',
        'user_id',
        'kos_id',
        'kamar_id',
        'booking_date',
        'start_date',
        'end_date',
        'rental_type',
        'price',
        'status',
        'notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'price' => 'decimal:2',
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
}
