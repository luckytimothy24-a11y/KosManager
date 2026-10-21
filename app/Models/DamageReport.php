<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DamageReport extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_REVIEWING = 'reviewing';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_RESOLVED = 'resolved';

    protected $fillable = [
        'user_id',
        'kos_id',
        'kamar_id',
        'category',
        'description',
        'photos',
        'status',
        'owner_reply',
        'rejection_reason',
        'owner_id',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kos()
    {
        return $this->belongsTo(Kos::class);
    }

    public function kamar()
    {
        return $this->belongsTo(Kamar::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
