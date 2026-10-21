<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';

    /**
     * Konfirmasi booking oleh owner/admin.
     * Nilai di DB adalah `approved` (legacy enum) = confirmed pada business rules.
     * Jangan gunakan STATUS_CONFIRMED ('confirmed') untuk persistensi — tidak ada di enum DB.
     */
    public const STATUS_APPROVED = 'approved';

    /** Alias domain: confirmed == approved di database. */
    public const STATUS_CONFIRMED = self::STATUS_APPROVED;

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
        'requested_occupants',
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
        'requested_occupants' => 'integer',
    ];

    protected static function booted(): void
    {
        // HARD RULE: tidak boleh ada implicit status transition.
        // Semua perubahan status harus valid menurut state machine di backend.
        static::saving(function (self $booking) {
            if (! $booking->exists || ! $booking->isDirty('status')) {
                return;
            }

            $from = $booking->getOriginal('status');
            $to = $booking->status;

            if ($from === null || $to === null || $from === $to) {
                return;
            }

            if (! $booking->canTransitionFrom($from, $to)) {
                Log::warning('Booking invalid status transition blocked', [
                    'booking_id' => $booking->id,
                    'from' => $from,
                    'to' => $to,
                ]);

                throw new \RuntimeException(
                    "Transisi status booking tidak valid: {$from} -> {$to}."
                );
            }
        });
    }

    public function occupants()
    {
        return $this->hasMany(BookingOccupant::class);
    }

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

    public function cancellationRequests()
    {
        return $this->hasMany(BookingCancellationRequest::class);
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }

    /**
     * Pembayaran aktif untuk booking ini (prerequisite konfirmasi).
     * Source of truth: pembayarans.verification_status.
     */
    public function activeBookingPayment()
    {
        return $this->hasOne(Pembayaran::class)
            ->whereIn('verification_status', Pembayaran::ACTIVE_VERIFICATIONS)
            ->latestOfMany();
    }

    public function approvedBookingPayment()
    {
        return $this->hasOne(Pembayaran::class)
            ->where('verification_status', Pembayaran::STATUS_APPROVED)
            ->latestOfMany();
    }

    /**
     * Prerequisite konfirmasi booking: minimal ada payment approved.
     */
    public function hasApprovedPayment(): bool
    {
        return Pembayaran::where('booking_id', $this->id)
            ->where('verification_status', Pembayaran::STATUS_APPROVED)
            ->exists();
    }

    /**
     * Status payment untuk UI/API — dibaca dari source of truth pembayarans.
     */
    public function paymentStatus(): string
    {
        $payment = Pembayaran::where('booking_id', $this->id)
            ->whereIn('verification_status', Pembayaran::ACTIVE_VERIFICATIONS)
            ->orderByDesc('id')
            ->first();

        if (! $payment) {
            return 'none';
        }

        return (string) $payment->verification_status;
    }

    public function latestCancellationRequest()
    {
        return $this->hasOne(BookingCancellationRequest::class)->latestOfMany();
    }

    public function pendingCancellationRequest()
    {
        return $this->hasOne(BookingCancellationRequest::class)
            ->where('status', BookingCancellationRequest::STATUS_PENDING)
            ->latestOfMany();
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
     * Booking confirmed (approved) yang belum check-in.
     */
    public function scopeNeedsCheckin(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED)->withoutActivePenghuni();
    }

    public static function cancellableStatuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_APPROVED];
    }

    /**
     * Status booking yang masih hidup (belum terminal).
     * Menurut rules: pending dan confirmed/approved masih dapat dibatalkan.
     */
    public static function activeStatuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_APPROVED];
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, static::cancellableStatuses(), true);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, static::terminalStatuses(), true);
    }

    /**
     * Status booking yang sudah terminal (tidak bisa kembali aktif).
     */
    public static function terminalStatuses(): array
    {
        return [self::STATUS_REJECTED, self::STATUS_CANCELLED, self::STATUS_COMPLETED, self::STATUS_EXPIRED];
    }

    /**
     * Status yang mengunci kamar sebagai reservasi (pending + approved).
     * Pending tidak berarti tenant confirmed, tetapi kamar tetap dipesan.
     */
    public static function roomReservingStatuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_APPROVED];
    }

    /**
     * Cek apakah transisi status valid dari status awal ke target.
     * Dilakukan di backend, bukan boleh dari frontend.
     */
    public function canTransitionFrom(string $from, string $to): bool
    {
        if ($from === $to) {
            return false;
        }

        // Terminal tidak boleh kembali kecuali cancelled (hanya dari expired legacy path).
        if (in_array($from, static::terminalStatuses(), true) && $to !== self::STATUS_CANCELLED) {
            // expired -> cancelled diizinkan untuk data legacy cleanup; selain itu tolak.
            if (! ($from === self::STATUS_EXPIRED && $to === self::STATUS_CANCELLED)) {
                return false;
            }
        }

        $transitions = $this->getAllowedTransitions();

        return in_array($to, $transitions[$from] ?? [], true);
    }

    /**
     * Cek apakah booking saat ini boleh berpindah ke target.
     */
    public function canTransitionTo(string $targetStatus): bool
    {
        return $this->canTransitionFrom((string) $this->status, $targetStatus);
    }

    /**
     * Daftar transisi yang diizinkan per status saat ini.
     * Semua transisi harus melalui backend validation / BookingStatusService.
     */
    public function getAllowedTransitions(): array
    {
        return [
            self::STATUS_PENDING => [
                self::STATUS_APPROVED,
                self::STATUS_REJECTED,
                self::STATUS_CANCELLED,
                self::STATUS_EXPIRED,
            ],
            self::STATUS_APPROVED => [
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED,
                self::STATUS_EXPIRED,
            ],
            self::STATUS_REJECTED => [],
            self::STATUS_CANCELLED => [],
            self::STATUS_COMPLETED => [],
            self::STATUS_EXPIRED => [self::STATUS_CANCELLED],
        ];
    }

    /**
     * Transisi terkontrol. Mengembalikan false bila tidak valid.
     * Tidak mengubah status tenant/payment — hanya booking.
     */
    public function transitionTo(string $targetStatus, array $attributes = []): bool
    {
        if ($this->status === $targetStatus) {
            return false;
        }

        if (! $this->canTransitionTo($targetStatus)) {
            return false;
        }

        return $this->update(array_merge($attributes, ['status' => $targetStatus]));
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
            self::STATUS_PENDING => match ($this->paymentStatus()) {
                'none' => 'Booking dikirim. Silakan lakukan pembayaran untuk melanjutkan. Booking belum dikonfirmasi.',
                'pending' => 'Pembayaran sudah dikirim. Menunggu verifikasi pemilik kos. Booking belum dikonfirmasi.',
                'approved' => 'Pembayaran terverifikasi. Menunggu konfirmasi pemilik kos.',
                default => 'Booking Anda sedang menunggu konfirmasi dari pemilik kos.',
            },
            self::STATUS_APPROVED => 'Kamar telah dipesan untuk Anda. Datanglah ke kos pada tanggal mulai dan minta pengelola memproses check-in.',
            self::STATUS_COMPLETED => 'Anda sudah terdaftar sebagai penghuni. Tinjau kontrak dan tagihan sewa Anda.',
            self::STATUS_REJECTED => 'Booking ini ditolak oleh pengelola kos. Silakan cari kos lain atau buat booking baru.',
            self::STATUS_CANCELLED => 'Booking ini sudah dibatalkan. Buat booking baru jika Anda masih ingin menyewa.',
            self::STATUS_EXPIRED => 'Periode sewa booking ini telah berakhir tanpa check-in.',
            default => null,
        };
    }
}
