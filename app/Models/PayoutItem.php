<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payout_id',
        'pembayaran_id',
        'gross_rent',
        'penalty_excluded',
        'commission_type',
        'commission_rate',
        'commission_amount',
        'net_amount',
    ];

    protected $casts = [
        'gross_rent' => 'decimal:2',
        'penalty_excluded' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function payout()
    {
        return $this->belongsTo(Payout::class);
    }

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class);
    }
}
