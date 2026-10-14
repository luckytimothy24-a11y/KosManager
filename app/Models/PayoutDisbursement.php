<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutDisbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'payout_id',
        'idempotency_key',
        'amount',
        'status',
        'proof_path',
        'transferred_at',
        'verified_at',
        'verified_by',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transferred_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function payout()
    {
        return $this->belongsTo(Payout::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
