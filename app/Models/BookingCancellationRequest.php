<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Request pembatalan booking oleh tenant.
 *
 * PENTING:
 * - status di sini adalah CANCELLATION REQUEST status (pending/approved/rejected).
 * - BUKAN booking.status.
 * - Tenant request ≠ booking cancelled. Hanya owner approval yang membatalkan booking.
 */
class BookingCancellationRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'booking_id',
        'requested_by',
        'status',
        'reason',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
