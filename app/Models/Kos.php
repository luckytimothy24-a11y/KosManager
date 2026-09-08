<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kos extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kos';

    protected $fillable = [
        'owner_id',
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
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
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
}
