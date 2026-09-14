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
        'billing_mode',
        'custom_share_amount',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'rental_price' => 'decimal:2',
        'custom_share_amount' => 'decimal:2',
        'billing_mode' => 'string',
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

    public function isShared(): bool
    {
        return $this->effectiveBillingMode() === 'shared';
    }

    /**
     * Effective billing mode: kontrak override takes precedence, then kamar default.
     */
    public function effectiveBillingMode(): string
    {
        if ($this->billing_mode !== null) {
            return $this->billing_mode;
        }

        return $this->kamar->billing_mode ?? 'personal';
    }

    public function tagihans()
    {
        return $this->hasMany(Tagihan::class);
    }
}
