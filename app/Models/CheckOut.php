<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckOut extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    /**
     * Status check-out yang masih berjalan / sudah disetujui (menghalangi duplikasi).
     */
    public const ACTIVE = [self::STATUS_PENDING, self::STATUS_APPROVED];

    protected $fillable = [
        'penghuni_id',
        'kamar_id',
        'request_date',
        'check_out_date',
        'room_condition',
        'notes',
        'status',
        'verified_by',
    ];

    protected $casts = [
        'request_date' => 'date',
        'check_out_date' => 'date',
    ];

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class);
    }

    public function kamar()
    {
        return $this->belongsTo(Kamar::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE);
    }
}
