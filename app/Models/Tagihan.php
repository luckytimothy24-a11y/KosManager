<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tagihan extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_UNPAID = 'unpaid';

    public const STATUS_OVERDUE = 'overdue';

    public const STATUS_PAID = 'paid';

    public const STATUS_PAYMENT_PENDING = 'pending_verification';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Set status tagihan yang masih bisa dibayar (belum ada pembayaran aktif).
     */
    public const PAYABLE = [self::STATUS_UNPAID, self::STATUS_OVERDUE];

    /**
     * Set status tagihan yang "menghalangi" (check-out / auto-checkout / hapus kos)
     * karena masih menunggak atau sedang menunggu verifikasi pembayaran.
     */
    public const OUTSTANDING = [self::STATUS_UNPAID, self::STATUS_OVERDUE, self::STATUS_PAYMENT_PENDING];

    protected $fillable = [
        'bill_number',
        'penghuni_id',
        'kontrak_id',
        'kamar_id',
        'bill_type',
        'period_start',
        'period_end',
        'subtotal',
        'discount',
        'penalty',
        'total',
        'due_date',
        'status',
        'active_billing_key',
    ];

    protected static function booted(): void
    {
        // Saat soft-delete, lepas kunci billing agar baris yang dihapus tidak
        // memblokir pembuatan tagihan periode yang sama di kemudian hari.
        static::deleting(function (Tagihan $tagihan) {
            if (! $tagihan->trashed() && $tagihan->active_billing_key !== null) {
                $tagihan->forceFill(['active_billing_key' => null])->save();
            }
        });

        static::restoring(function (Tagihan $tagihan) {
            if ($tagihan->active_billing_key === null
                && $tagihan->kontrak_id
                && $tagihan->period_start
                && $tagihan->period_end) {
                $tagihan->forceFill([
                    'active_billing_key' => static::billingKey(
                        (int) $tagihan->kontrak_id,
                        $tagihan->period_start->format('Y-m-d'),
                        $tagihan->period_end->format('Y-m-d'),
                    ),
                ])->save();
            }
        });
    }

    public static function billingKey(int $kontrakId, string $periodStart, string $periodEnd): string
    {
        return $kontrakId.':'.$periodStart.':'.$periodEnd;
    }

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'penalty' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class);
    }

    public function kontrak()
    {
        return $this->belongsTo(Kontrak::class);
    }

    public function kamar()
    {
        return $this->belongsTo(Kamar::class);
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function scopePayable(Builder $query): Builder
    {
        return $query->whereIn('status', self::PAYABLE);
    }

    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->whereIn('status', self::OUTSTANDING);
    }
}
