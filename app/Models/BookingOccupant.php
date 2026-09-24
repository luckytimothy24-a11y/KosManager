<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingOccupant extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_INVITED = 'invited';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public const IDENTITY_NOT_SUBMITTED = 'not_submitted';
    public const IDENTITY_PENDING = 'pending';
    public const IDENTITY_CONFIRMED = 'confirmed';
    public const IDENTITY_REJECTED = 'rejected';

    protected $fillable = [
        'booking_id',
        'occupant_index',
        'user_id',
        'email',
        'display_name',
        'status',
        'invite_token_hash',
        'invite_expires_at',
        'accepted_at',
        'declined_at',
        'identity_status',
        'identity_number',
        'phone',
        'address',
        'identity_data_submitted_at',
        'identity_confirmed_at',
        'identity_confirmed_by',
        'identity_rejected_at',
        'identity_rejection_reason',
        'penghuni_id',
    ];

    protected $casts = [
        'occupant_index' => 'integer',
        'invite_expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'identity_data_submitted_at' => 'datetime',
        'identity_confirmed_at' => 'datetime',
        'identity_rejected_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'identity_confirmed_by');
    }

    public function isInviteExpired(): bool
    {
        return $this->invite_expires_at !== null && $this->invite_expires_at->isPast();
    }

    public function isTokenValid(): bool
    {
        return $this->status === self::STATUS_INVITED
            && $this->invite_token_hash !== null
            && ! $this->isInviteExpired();
    }

    public function identityPending(): bool
    {
        return $this->identity_status === self::IDENTITY_PENDING;
    }

    public function identityConfirmed(): bool
    {
        return $this->identity_status === self::IDENTITY_CONFIRMED;
    }

    public function hasIdentityData(): bool
    {
        return $this->identity_data_submitted_at !== null
            && filled($this->identity_number)
            && filled($this->phone)
            && filled($this->address);
    }

    public function maskedIdentityNumber(): string
    {
        $num = (string) ($this->identity_number ?? '');
        if ($num === '') return '-';
        $len = mb_strlen($num);
        if ($len <= 4) return str_repeat('*', $len);
        return str_repeat('*', $len - 4).mb_substr($num, -4);
    }
}
