<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'buyer_id',
        'seller_id',
        'price',
        'status',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public function item(): BelongsTo
    {
        return $this->belongsTo(MarketplaceItem::class, 'item_id')->withTrashed();
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
