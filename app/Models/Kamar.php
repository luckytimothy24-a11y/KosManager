<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kamar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kamar';

    protected static function booted(): void
    {
        static::creating(function (Kamar $kamar) {
            if ($kamar->billing_mode === null) {
                $kamar->billing_mode = 'personal';
            }
        });
    }

    protected $fillable = [
        'kos_id',
        'room_number',
        'room_name',
        'floor',
        'room_type',
        'capacity',
        'billing_mode',
        'daily_price',
        'weekly_price',
        'monthly_price',
        'area',
        'description',
        'photo',
        'status',
    ];

    protected $casts = [
        'daily_price' => 'decimal:2',
        'weekly_price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'capacity' => 'integer',
        'billing_mode' => 'string',
    ];

    public function occupancy(): int
    {
        return Penghuni::where('kamar_id', $this->id)->where('status', 'active')->count();
    }

    public function isFull(): bool
    {
        return $this->occupancy() >= (int) $this->capacity;
    }

    public function isShared(): bool
    {
        return $this->effectiveBillingMode() === 'shared';
    }

    public function isPersonal(): bool
    {
        return $this->effectiveBillingMode() === 'personal';
    }

    public function activeKontraks()
    {
        return $this->kontraks()->where('status', 'active');
    }

    public function remainingSlots(): int
    {
        return max(0, (int) $this->capacity - $this->occupancy());
    }

    public function hasAvailableSlot(): bool
    {
        return $this->remainingSlots() > 0;
    }

    public function isAvailableForBooking(): bool
    {
        if ($this->status === 'maintenance') {
            return false;
        }

        // Shared: slot-aware (occupancy < capacity) even if status occupied/booked
        if (($this->billing_mode ?? 'personal') === 'shared') {
            return $this->hasAvailableSlot();
        }

        // Personal: only status available is bookable (backward compat for existing tests/fixtures)
        return $this->status === 'available';
    }

    /**
     * Scope kamar yang masih memiliki slot tersedia — occupancy-aware for shared.
     * Personal: status === available.
     * Shared: occupancy < capacity (meski status occupied/booked), not maintenance.
     * SQL-safe untuk whereHas / withCount.
     */
    public function scopeWithAvailableSlot($query)
    {
        return $query->where('status', '!=', 'maintenance')
            ->where(function ($q) {
                $q->where('status', 'available')
                  ->orWhere(function ($q2) {
                      $q2->where('billing_mode', 'shared')
                         ->whereRaw('(SELECT COUNT(*) FROM penghunis WHERE penghunis.kamar_id = kamar.id AND penghunis.status = "active" AND penghunis.deleted_at IS NULL) < kamar.capacity');
                  });
            });
    }

    /**
     * Scope SQL untuk kamar yang benar-benar bisa dibooking (occupancy < capacity).
     * Alias for withAvailableSlot — digunakan di listing marketplace agar kamar shared 1/2 tetap muncul sebagai tersedia.
     */
    public function scopeAvailableForBooking($query)
    {
        return $query->withAvailableSlot();
    }

    /**
     * Effective billing mode: kontrak override takes precedence, then kamar default.
     */
    public function effectiveBillingMode(?Kontrak $kontrak = null): string
    {
        if ($kontrak && $kontrak->billing_mode !== null) {
            return $kontrak->billing_mode;
        }

        return $this->billing_mode ?? 'personal';
    }

    public function kos()
    {
        return $this->belongsTo(Kos::class);
    }

    public function fasilitas()
    {
        return $this->belongsToMany(Fasilitas::class, 'kamar_fasilitas');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function kontraks()
    {
        return $this->hasMany(Kontrak::class);
    }

    public function penghunis()
    {
        return $this->hasMany(Penghuni::class);
    }

    public function checkIns()
    {
        return $this->hasMany(CheckIn::class);
    }

    public function checkOuts()
    {
        return $this->hasMany(CheckOut::class);
    }

    public function tagihans()
    {
        return $this->hasMany(Tagihan::class);
    }
}
