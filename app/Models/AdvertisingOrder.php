<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisingOrder extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_REFUNDED = 'refunded';

    protected $table = 'advertising_orders';

    protected $fillable = [
        'order_number',
        'campaign_id',
        'owner_id',
        'amount',
        'status',
        'paid_at',
        'paid_by',
        'refunded_at',
        'refunded_by',
        'refund_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(AdvertisingCampaign::class, 'campaign_id')->withTrashed();
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id')->withTrashed();
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    protected static function booted(): void
    {
        // Revenue record: cannot be deleted
        static::deleting(function (AdvertisingOrder $order) {
            throw new \RuntimeException('Order iklan adalah record revenue yang tidak dapat dihapus.');
        });
    }
}
