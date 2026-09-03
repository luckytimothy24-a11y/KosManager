<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRole, Notifiable, SoftDeletes;

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
        'avatar',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function ownedKos()
    {
        return $this->hasMany(Kos::class, 'owner_id');
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

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function hasFavorited(int $kosId): bool
    {
        return $this->favorites()->where('kos_id', $kosId)->exists();
    }
}
