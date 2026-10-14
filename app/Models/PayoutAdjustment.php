<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payout_id',
        'pembayaran_id',
        'type',
        'amount',
        'commission_delta',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_delta' => 'decimal:2',
    ];

    public function payout()
    {
        return $this->belongsTo(Payout::class);
    }

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
