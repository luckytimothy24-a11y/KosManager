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
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'rent_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'verified_at' => 'datetime',
        'gateway_expires_at' => 'datetime',
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
     * Distribution record (booking payment → owner).
     */
    public function paymentDistribution()
    {
        return $this->hasOne(PaymentDistribution::class, 'pembayaran_id');
    }

    /**
     * Item payout yang mengaitkan pembayaran ini ke payout.
     */
    public function payoutItems()
    {
        return $this->hasMany(PayoutItem::class, 'pembayaran_id');
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

    public function isRejected(): bool
    {
        return $this->verification_status === self::STATUS_REJECTED;
    }

    /**
     * Nominal yang diharapkan server (bukan request client).
     */
    public function expectedAmount(): float
    {
        if ($this->isBookingPayment() && $this->booking) {
            return (float) $this->booking->price;
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

    protected static function booted(): void
    {
        static::forceDeleting(function (Pembayaran $pembayaran) {
            // Guard: prevent force delete if payment has distribution or payout items
            if ($pembayaran->paymentDistribution()->exists() ||
                $pembayaran->payoutItems()->exists()) {
                throw new \RuntimeException('Pembayaran memiliki distribusi atau item payout. Tidak dapat dihapus permanen.');
            }
        });
    }
}
