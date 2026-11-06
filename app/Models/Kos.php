<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kos extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kos';

    protected $fillable = [
        'owner_id',
        'property_owner_id',
        'name',
        'address',
        'latitude',
        'longitude',
        'description',
        'phone',
        'photo',
        'general_facilities',
        'rules',
        'payment_info',
        'status',
        'auto_billing_enabled',
        'billing_day',
        'due_days',
        'moderation_status',
        'rejection_reason',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'auto_billing_enabled' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Pemilik Kos Asli (property owner) — pihak penerima payout.
     * BERBEDA dari owner() (pengelola/partner role owner).
     */
    public function propertyOwner()
    {
        return $this->belongsTo(PropertyOwner::class);
    }

    public function kamar()
    {
        return $this->hasMany(Kamar::class);
    }

    public function admins()
    {
        return $this->belongsToMany(User::class, 'kos_user');
    }

    public function fasilitas()
    {
        return $this->belongsToMany(Fasilitas::class, 'kos_fasilitas');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function penghunis()
    {
        return $this->hasMany(Penghuni::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function advertisingCampaigns()
    {
        return $this->hasMany(AdvertisingCampaign::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    public function managementAgreements()
    {
        return $this->hasMany(ManagementAgreement::class);
    }

    public function pembayarans()
    {
        // pembayarans don't have kos_id; go through penghuni
        return $this->hasManyThrough(Pembayaran::class, Penghuni::class, 'kos_id', 'penghuni_id', 'id', 'id');
    }

    public function photos()
    {
        return $this->hasMany(KosPhoto::class)->orderBy('position');
    }

    public function coverPhoto()
    {
        return $this->hasOne(KosPhoto::class)->where('is_cover', true);
    }

    public function coverPath(): ?string
    {
        // Jangan query ulang bila relasi sudah di-eager-load (walau null) —
        // menghemat 2 query per kos pada listing (N+1).
        if ($this->relationLoaded('coverPhoto')) {
            if ($this->coverPhoto) {
                return $this->coverPhoto->path;
            }
        } else {
            $cover = $this->coverPhoto()->first();

            if ($cover) {
                return $cover->path;
            }
        }

        $firstPhoto = $this->relationLoaded('photos')
            ? $this->photos->first()
            : $this->photos()->first();

        if ($firstPhoto) {
            return $firstPhoto->path;
        }

        return $this->photo;
    }

    public function galleryPhotoPaths(): array
    {
        $photos = $this->relationLoaded('photos')
            ? $this->photos
            : $this->photos()->get();

        if ($photos->isNotEmpty()) {
            return $photos->pluck('path')->all();
        }

        return $this->photo ? [$this->photo] : [];
    }

    protected static function booted(): void
    {
        static::forceDeleting(function (Kos $kos) {
            // Guard: prevent force delete if there's monetization history
            if ($kos->payouts()->exists() ||
                $kos->managementAgreements()->exists() ||
                $kos->advertisingCampaigns()->exists() ||
                $kos->pembayarans()->exists() ||
                $kos->kamar()->exists() ||
                $kos->bookings()->exists() ||
                $kos->penghunis()->exists()) {
                throw new \RuntimeException('Kos memiliki riwayat payout, komisi, iklan, atau pembayaran. Nonaktifkan kos sebagai gantinya.');
            }

            // Clean up gallery files
            $paths = $kos->photos()->pluck('path')->all();
            foreach ($paths as $path) {
                try {
                    \Storage::disk('public')->delete($path);
                } catch (\Throwable $e) {
                    \Log::warning('Kos forceDeleting gallery cleanup failed', ['kos_id' => $kos->id, 'path' => $path, 'error' => $e->getMessage()]);
                }
            }
        });
    }

    public function favoriteCount(): int
    {
        return $this->favorites()->count();
    }

    public function hasValidCoordinates(): bool
    {
        if (! is_numeric($this->latitude) || ! is_numeric($this->longitude)) {
            return false;
        }

        if ((float) $this->latitude < -90 || (float) $this->latitude > 90) {
            return false;
        }

        if ((float) $this->longitude < -180 || (float) $this->longitude > 180) {
            return false;
        }

        return true;
    }

    public function coordinatesDestination(): ?string
    {
        if (! $this->hasValidCoordinates()) {
            return null;
        }

        return trim((string) $this->latitude).','.trim((string) $this->longitude);
    }

    public function addressDestination(): ?string
    {
        $address = is_string($this->address) ? trim($this->address) : '';

        return $address !== '' ? $address : null;
    }

    public function routeDestination(): ?string
    {
        return $this->coordinatesDestination() ?? $this->addressDestination();
    }

    public function googleMapsDestinationUrl(string $mode): ?string
    {
        $coordinates = $this->coordinatesDestination();

        if ($coordinates !== null) {
            $destination = urlencode((string) $this->latitude).','.urlencode((string) $this->longitude);
        } else {
            $address = $this->addressDestination();

            if ($address === null) {
                return null;
            }

            $destination = urlencode($address);
        }

        return 'https://www.google.com/maps/'.$mode.'/?api=1&destination='.$destination;
    }

    public function googleMapsDirectionsUrl(): ?string
    {
        return $this->googleMapsDestinationUrl('dir');
    }

    public function googleMapsSearchUrl(): ?string
    {
        $coordinates = $this->coordinatesDestination();

        if ($coordinates !== null) {
            $query = urlencode((string) $this->latitude).','.urlencode((string) $this->longitude);
        } else {
            $address = $this->addressDestination();

            if ($address === null) {
                return null;
            }

            $query = urlencode($address);
        }

        return 'https://www.google.com/maps/search/?api=1&query='.$query;
    }

    /**
     * Hitung tanggal billing berikutnya berdasarkan billing_day.
     * Jika billing_day bulan ini sudah lewat, ambil bulan depan.
     */
    public function nextBillingDate(): Carbon
    {
        $now = Carbon::today();
        $candidate = $now->copy()->day(min($this->billing_day, $now->daysInMonth));

        if ($now->greaterThan($candidate)) {
            $candidate->addMonthNoOverflow();
            $candidate->day(min($this->billing_day, $candidate->daysInMonth));
        }

        return $candidate;
    }
}
