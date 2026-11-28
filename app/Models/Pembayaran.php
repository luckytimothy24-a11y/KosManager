<?php

namespace App\Models;

use App\Traits\HasIntegerRupiah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembayaran extends Model
{
    use HasFactory, HasIntegerRupiah, SoftDeletes;

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
        'amount_rp' => 'integer',
        'rent_amount_rp' => 'integer',
        'penalty_amount_rp' => 'integer',
        'refund_amount_rp' => 'integer',
    ];

    /**
     * @var array<string, string>
     */
    protected array $rupiahColumns = [
        'amount' => 'amount_rp',
        'rent_amount' => 'rent_amount_rp',
        'penalty_amount' => 'penalty_amount_rp',
        'refund_amount' => 'refund_amount_rp',
    ];

    public function getAmountAttribute($value): ?int
    {
        return $this->getRupiahValue('amount', $value);
    }

    public function setAmountAttribute($value): void
    {
        $this->setRupiahValue('amount', $value);
    }

    public function getRentAmountAttribute($value): ?int
    {
        return $this->getRupiahValue('rent_amount', $value);
    }

    public function setRentAmountAttribute($value): void
    {
        $this->setRupiahValue('rent_amount', $value);
    }

    public function getPenaltyAmountAttribute($value): ?int
    {
        return $this->getRupiahValue('penalty_amount', $value);
    }

    public function setPenaltyAmountAttribute($value): void
    {
        $this->setRupiahValue('penalty_amount', $value);
    }

    public function getRefundAmountAttribute($value): ?int
    {
        return $this->getRupiahValue('refund_amount', $value);
    }

    public function setRefundAmountAttribute($value): void
    {
        $this->setRupiahValue('refund_amount', $value);
    }

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
     * Cek apakah refund masih bisa di-approve (status requested).
     */
    public function isRefundApprovable(): bool
    {
        return $this->refund_status === self::REFUND_REQUESTED;
    }

    /**
     * Cek apakah refund sudah di-approve tapi belum completed.
     */
    public function isRefundApproved(): bool
    {
        return $this->refund_status === self::REFUND_APPROVED;
    }

    /**
     * Cek apakah refund ditolak.
     */
    public function isRefundRejected(): bool
    {
        return $this->refund_status === self::REFUND_REJECTED;
    }

    /**
     * Cek apakah refund completed.
     */
    public function isRefundCompleted(): bool
    {
        return $this->refund_status === self::REFUND_COMPLETED;
    }

    /**
     * Valid transisi status refund.
     * - requested → approved | rejected
     * - approved → completed TIDAK diizinkan pada Phase 4.0A
     *   (menunggu mekanisme bukti transfer di fase berikutnya)
     * - completed dan rejected adalah terminal
     */
    public function isValidRefundTransition(string $targetStatus): bool
    {
        $current = $this->refund_status;

        if ($current === self::REFUND_REQUESTED) {
            return in_array($targetStatus, [self::REFUND_APPROVED, self::REFUND_REJECTED]);
        }

        return false;
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

    /**
     * Kebijakan riwayat bukti bayar:
     * - Record pembayaran TIDAK dihapus (audit trail).
     * - Saat proof_file diganti pada record yang sama, file lama dihapus
     *   agar tidak ada bukti yatim (orphan) di disk privat.
     * - Purge file pembayaran rejected yang lama ditangani command
     *   payments:purge-rejected-proofs.
     */
    public function setProofFileAttribute(?string $value): void
    {
        if ($this->exists && $value !== null) {
            $oldProof = $this->getOriginal('proof_file');

            if ($oldProof && $oldProof !== $value) {
                try {
                    \Storage::disk('private')->delete($oldProof);
                } catch (\Throwable $e) {
                    \Log::warning('Failed to delete replaced payment proof: '.$e->getMessage());
                }
            }
        }

        $this->attributes['proof_file'] = $value;
    }
}
