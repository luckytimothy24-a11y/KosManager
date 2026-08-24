<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembayaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_number',
        'tagihan_id',
        'penghuni_id',
        'amount',
        'payment_date',
        'payment_method',
        'proof_file',
        'verification_status',
        'admin_notes',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
