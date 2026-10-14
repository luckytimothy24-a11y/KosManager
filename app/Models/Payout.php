<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Payout/Settlement ke Pemilik Kos Asli.
 *
 * Alur: Owner menghitung kewajiban → transfer manual di luar aplikasi →
 * upload bukti (transferred) → Super Admin verifikasi (verified/rejected).
 * rejected → Owner resubmit record yang SAMA (bukan payout baru).
 *
 * TERPISAH dari PaymentDistribution (distribusi booking → hak role Owner).
 *
 * platform_fee MVP selalu 0 — tidak editable Owner, tidak dari request.
 * amount = hak Pemilik Kos Asli = gross_amount - platform_fee.
 * Nominal dihitung SERVER-SIDE — jangan pernah mass-assign dari request.
 */
class Payout extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_TRANSFERRED = 'transferred';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'payout_number',
        'kos_id',
        'owner_id',
        'property_owner_id',
        'gross_amount',
        'platform_fee',
        'commission_total',
        'penalty_excluded_total',
        'amount',
        'period_start',
        'period_end',
        'status',
        'transferred_at',
        'verified_at',
        'verified_by',
        'proof_path',
        'notes',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'commission_total' => 'decimal:2',
        'penalty_excluded_total' => 'decimal:2',
        'amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'transferred_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function kos()
    {
        return $this->belongsTo(Kos::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function propertyOwner()
    {
        return $this->belongsTo(PropertyOwner::class);
    }

    public function propertyOwnerBankAccount()
    {
        return $this->belongsTo(PropertyOwnerBankAccount::class, 'property_owner_id', 'property_owner_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function items()
    {
        return $this->hasMany(PayoutItem::class);
    }

    public function adjustments()
    {
        return $this->hasMany(PayoutAdjustment::class);
    }

    public function disbursements()
    {
        return $this->hasMany(PayoutDisbursement::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isTransferred(): bool
    {
        return $this->status === self::STATUS_TRANSFERRED;
    }

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Status yang masih boleh di-transfer (pending baru / rejected resubmit).
     */
    public static function transferableStatuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_REJECTED];
    }
}
