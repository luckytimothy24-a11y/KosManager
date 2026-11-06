<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Distribusi pembayaran booking → hak Owner.
 *
 * Terpisah dari:
 * - pembayarans.verification_status (payment verification)
 * - bookings.status (booking confirmation)
 *
 * platform_fee = 0 bila tidak ada business rule fee existing.
 */
class PaymentDistribution extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_READY = 'ready';

    public const STATUS_PROCESSED = 'processed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'pembayaran_id',
        'booking_id',
        'owner_id',
        'gross_amount',
        'platform_fee',
        'owner_amount',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'owner_amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(OwnerBankAccount::class, 'owner_id', 'owner_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isReady(): bool
    {
        return $this->status === self::STATUS_READY;
    }

    public function isProcessed(): bool
    {
        return $this->status === self::STATUS_PROCESSED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Status yang masih "aktif" (belum selesai) untuk cek double payout.
     */
    public static function activeStatuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_READY, self::STATUS_PROCESSED];
    }
}
