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
    ];

    protected $casts = [
        'position' => 'integer',
        'is_cover' => 'boolean',
    ];

    public function kos(): BelongsTo
    {
        return $this->belongsTo(Kos::class);
    }
}
