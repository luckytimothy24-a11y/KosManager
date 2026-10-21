<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KosPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'kos_id',
        'path',
        'position',
        'is_cover',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_cover' => 'boolean',
    ];

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PENDING = 'pending';

    public const STATUS_REJECTED = 'rejected';

    public function kos(): BelongsTo
    {
        return $this->belongsTo(Kos::class);
    }
}
