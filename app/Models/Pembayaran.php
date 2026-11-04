<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembayaran extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Source of truth status pembayaran (verification_status di DB).
     * Satu state machine: pending → approved | rejected.
     * TIDAK ada field payment_status terpisah.
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    // Refund status constants
    public const REFUND_NONE = 'none';
    public const REFUND_REQUESTED = 'requested';
    public const REFUND_APPROVED = 'approved';
    public const REFUND_REJECTED = 'rejected';
    public const REFUND_COMPLETED = 'completed';

    /**
     * Status verifikasi yang berarti pembayaran aktif / sudah terverifikasi
     * (menghalangi upload ulang).
     */
    public const ACTIVE_VERIFICATIONS = [self::STATUS_PENDING, self::STATUS_APPROVED];

    protected $fillable = [
        'payment_number',
        'tagihan_id',
        'booking_id',
        'penghuni_id',
        'amount',
        'rent_amount',
        'penalty_amount',
        'payment_date',
        'payment_method',
        'proof_file',
        'verification_status',
        'admin_notes',
        'verified_by',
        'notes',
        'verified_at',
        'gateway_provider',
        'gateway_reference',
        'gateway_instructions',
        'gateway_expires_at',
        'active_payment_key',
        'refund_status',
        'refund_amount',
        'refunded_at',
        'refund_reason',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'rent_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'verified_at' => 'datetime',
        'gateway_expires_at' => 'datetime',
        'refunded_at' => 'datetime',
        'refund_amount' => 'decimal:2',
    ];

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Pembayaran terkait booking (deposit/sewa booking sebelum check-in).
     */
    public function isBookingPayment(): bool
    {
        return $this->booking_id !== null;
    }

    public function isPending(): bool
    {
        return $this->verification_status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->verification_status === self::STATUS_APPROVED;
    }

    public function isRefunded(): bool
    {
        return $this->refund_status === self::REFUND_COMPLETED;
    }

    public function isRefundRequested(): bool
    {
        return $this->refund_status === self::REFUND_REQUESTED;
    }

    /**
     * Nominal yang diharapkan server (bukan request client).
     */
    public function expectedAmount(): float
    {
        if ($this->isBookingPayment() && $this->booking) {
            return $this->booking->expectedBookingPaymentAmount();
        }

        return (float) ($this->tagihan?->total ?? 0);
    }

    /**
     * Apakah pembayaran ini dibuat lewat gateway online (verifikasi otomatis).
     */
    public function isFromGateway(): bool
    {
        return $this->gateway_provider !== null;
    }
}
