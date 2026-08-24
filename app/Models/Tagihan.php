<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tagihan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bill_number',
        'penghuni_id',
        'kontrak_id',
        'kamar_id',
        'bill_type',
        'period_start',
        'period_end',
        'subtotal',
        'discount',
        'penalty',
        'total',
        'due_date',
        'status',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'penalty' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class);
    }

    public function kontrak()
    {
        return $this->belongsTo(Kontrak::class);
    }

    public function kamar()
    {
        return $this->belongsTo(Kamar::class);
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }
}
