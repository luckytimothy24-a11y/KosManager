<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasEncryptedIdentityNumber;
use App\Traits\HasRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasEncryptedIdentityNumber, HasFactory, HasRole, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'identity_number',
        'avatar',
        'is_active',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'two_factor_secret' => 'encrypted',
        'two_factor_recovery_codes' => 'encrypted:array',
        'two_factor_confirmed_at' => 'datetime',
    ];

    /**
     * Apakah 2FA sudah aktif dan terkonfirmasi.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null && $this->two_factor_secret !== null;
    }

    /**
     * Role yang dianjurkan/diwajibkan memakai 2FA.
     */
    public function twoFactorRequired(): bool
    {
        if (! config('two-factor.enforce', false)) {
            return false;
        }

        $roles = config('two-factor.enforce_roles', ['super_admin', 'admin']);

        return in_array($this->role, $roles, true);
    }

    public function ownedKos()
    {
        return $this->hasMany(Kos::class, 'owner_id');
    }

    public function ownerProfile()
    {
        return $this->hasOne(OwnerProfile::class, 'user_id');
    }

    public function bankAccount()
    {
        return $this->hasOne(OwnerBankAccount::class, 'owner_id');
    }

    public function paymentDistributions()
    {
        return $this->hasMany(PaymentDistribution::class, 'owner_id');
    }

    /**
     * Payout ke Pemilik Kos Asli yang dibuat/dikelola oleh user ini (owner).
     */
    public function payouts()
    {
        return $this->hasMany(Payout::class, 'owner_id');
    }

    public function assignedKos()
    {
        return $this->belongsToMany(Kos::class, 'kos_user');
    }

    public function canAccessKos(int $kosId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isOwner()) {
            return $this->ownedKos()->whereKey($kosId)->exists();
        }

        if ($this->isAdmin()) {
            return $this->assignedKos()->whereKey($kosId)->exists();
        }

        return false;
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function penghunis()
    {
        return $this->hasMany(Penghuni::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function advertisingCampaigns()
    {
        return $this->hasMany(AdvertisingCampaign::class, 'owner_id');
    }

    public function advertisingOrders()
    {
        return $this->hasMany(AdvertisingOrder::class, 'owner_id');
    }

    public function advertisingEvents()
    {
        return $this->hasMany(AdvertisingEvent::class, 'user_id');
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function hasFavorited(int $kosId): bool
    {
        return $this->favorites()->where('kos_id', $kosId)->exists();
    }
}
