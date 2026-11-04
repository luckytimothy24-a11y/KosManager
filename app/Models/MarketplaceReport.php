<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'reporter_id',
        'reason',
        'description',
        'status',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(MarketplaceItem::class, 'item_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}