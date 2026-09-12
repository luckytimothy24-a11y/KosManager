<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'booking_code',
        'user_id',
        'kos_id',
        'kamar_id',
        'booking_date',
        'start_date',
        'end_date',
        'rental_type',
        'billing_mode',
        'price',
        'status',
        'identity_confirmed_at',
        'identity_confirmed_by',
        'identity_rejected_at',
        'identity_rejection_reason',
        'identity_number',
        'phone',
        'address',
        'identity_data_submitted_at',
        'notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'identity_confirmed_at' => 'datetime',
        'identity_confirmed_by' => 'integer',
        'identity_rejected_at' => 'datetime',
        'identity_data_submitted_at' => 'datetime',
        'price' => 'decimal:2',
        'billing_mode' => 'string',
    ];

    public function identityConfirmed(): bool
    {
        return $this->identity_confirmed_at !== null;
    }

    public function identityRejected(): bool
    {
        return $this->identity_rejected_at !== null;
    }

    public function identityPending(): bool
    {
        return $this->identity_data_submitted_at !== null
            && $this->identity_confirmed_at === null
            && $this->identity_rejected_at === null;
    }

    /**
     * Human status for identity check-in flow (4 states).
     * - not_submitted | pending | confirmed | rejected
     */
    public function identityStatus(): string
    {
        if ($this->identityConfirmed()) {
            return 'confirmed';
        }
        if ($this->identityRejected()) {
            return 'rejected';
        }
        if ($this->identity_data_submitted_at !== null) {
            return 'pending';
        }

        return 'not_submitted';
    }

    /**
     * Mask KTP: ************1234 — never log or expose full number unnecessarily.
     */
    public function maskedIdentityNumber(): string
    {
        $num = (string) ($this->identity_number ?? '');
        if ($num === '') {
            return '-';
        }
        $len = mb_strlen($num);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        return str_repeat('*', $len - 4).mb_substr($num, -4);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'identity_confirmed_by');
    }

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

    public function scopeWithoutActivePenghuni(Builder $query): Builder
    {
        return $query->whereNotExists(function ($sub) {
            $sub->select('id')
                ->from('penghunis')
                ->where('penghunis.status', 'active')
                ->whereColumn('penghunis.user_id', 'bookings.user_id')
                ->whereColumn('penghunis.kamar_id', 'bookings.kamar_id');
        });
    }

    /**
     * Booking aktif yang belum masuk (approved + belum punya penghuni aktif).
     */
    public function scopeNeedsCheckin(Builder $query): Builder
    {
        return $query->where('status', 'approved')->withoutActivePenghuni();
    }

    public static function cancellableStatuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_APPROVED];
    }

    /**
     * Status booking yang masih hidup (belum terminal).
     */
    public static function activeStatuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_APPROVED];
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, static::cancellableStatuses());
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, static::terminalStatuses());
    }

    /**
     * Status booking yang sudah terminal (tidak bisa kembali aktif).
     */
    public static function terminalStatuses(): array
    {
        return [self::STATUS_CANCELLED, self::STATUS_REJECTED, self::STATUS_EXPIRED];
    }

    public function hasIdentityData(): bool
    {
        return $this->identity_data_submitted_at !== null
            && filled($this->identity_number)
            && filled($this->phone)
            && filled($this->address);
    }

    public function priceUnitLabel(): string
    {
        return match ($this->rental_type) {
            'daily' => 'per hari',
            'weekly' => 'per minggu',
            'monthly' => 'per bulan',
            default => 'per bulan',
        };
    }

    public function nextAction(): ?string
    {
        return match ($this->status) {
            'pending' => 'Booking Anda sedang menunggu konfirmasi dari pemilik kos.',
            'approved' => 'Kamar telah dipesan untuk Anda. Datanglah ke kos pada tanggal mulai dan minta pengelola memproses check-in.',
            'completed' => 'Anda sudah terdaftar sebagai penghuni. Tinjau kontrak dan tagihan sewa Anda.',
            'rejected' => 'Booking ini ditolak oleh pengelola kos. Silakan cari kos lain atau buat booking baru.',
            'cancelled' => 'Booking ini sudah dibatalkan. Buat booking baru jika Anda masih ingin menyewa.',
            'expired' => 'Periode sewa booking ini telah berakhir tanpa check-in.',
            default => null,
        };
    }
}
