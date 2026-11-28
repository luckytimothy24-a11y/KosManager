<?php

namespace App\Models;

use App\Traits\HasIntegerRupiah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kontrak extends Model
{
    use HasFactory, HasIntegerRupiah, SoftDeletes;

    protected $fillable = [
        'contract_number',
        'penghuni_id',
        'kos_id',
        'kamar_id',
        'rental_type',
        'rental_price',
        'down_payment_amount',
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
        'down_payment_amount' => 'decimal:2',
        'custom_share_amount' => 'decimal:2',
        'rental_price_rp' => 'integer',
        'down_payment_amount_rp' => 'integer',
        'custom_share_amount_rp' => 'integer',
        'billing_mode' => 'string',
    ];

    /**
     * @var array<string, string>
     */
    protected array $rupiahColumns = [
        'rental_price' => 'rental_price_rp',
        'down_payment_amount' => 'down_payment_amount_rp',
        'custom_share_amount' => 'custom_share_amount_rp',
    ];

    public function getRentalPriceAttribute($value): ?int
    {
        return $this->getRupiahValue('rental_price', $value);
    }

    public function setRentalPriceAttribute($value): void
    {
        $this->setRupiahValue('rental_price', $value);
    }

    public function getDownPaymentAmountAttribute($value): ?int
    {
        return $this->getRupiahValue('down_payment_amount', $value);
    }

    public function setDownPaymentAmountAttribute($value): void
    {
        $this->setRupiahValue('down_payment_amount', $value);
    }

    public function getCustomShareAmountAttribute($value): ?int
    {
        return $this->getRupiahValue('custom_share_amount', $value);
    }

    public function setCustomShareAmountAttribute($value): void
    {
        $this->setRupiahValue('custom_share_amount', $value);
    }

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
