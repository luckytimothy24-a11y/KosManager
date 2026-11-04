<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketplaceItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'kos_id',
        'title',
        'description',
        'price',
        'condition',
        'category',
        'status',
        'image_path',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_SOLD = 'sold';

    public const CONDITION_NEW = 'baru';
    public const CONDITION_USED = 'bekas';
    public const CONDITION_SLIGHTLY_DAMAGED = 'rusak_ringan';

    public const CATEGORY_ELECTRONICS = 'elektronik';
    public const CATEGORY_FURNITURE = 'furniture';
    public const CATEGORY_CLOTHING = 'pakaian';
    public const CATEGORY_BOOKS = 'buku';
    public const CATEGORY_OTHER = 'lainnya';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kos(): BelongsTo
    {
        return $this->belongsTo(Kos::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MarketplaceTransaction::class, 'item_id');
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE && $this->deleted_at === null;
    }

    public function isSold(): bool
    {
        return $this->status === self::STATUS_SOLD;
    }
}