<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kontrak extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contract_number',
        'penghuni_id',
        'kos_id',
        'kamar_id',
        'rental_type',
        'rental_price',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'rental_price' => 'decimal:2',
    ];

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class);
    }

    public function kos()
    {
        return $this->belongsTo(Kos::class);
    }

    public function kamar()
    {
        return $this->belongsTo(Kamar::class);
    }

    public function tagihans()
    {
        return $this->hasMany(Tagihan::class);
    }
}
