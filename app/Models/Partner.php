<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Partner extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $table = 'partners';

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'description',
        'website_url',
        'monetization_type',
        'contact_name',
        'contact_email',
        'status',
    ];

    protected static function booted(): void
    {
        static::saving(function (Partner $partner) {
            if (! $partner->slug) {
                $partner->slug = Str::slug($partner->name);
            }
        });
    }

    public function campaigns()
    {
        return $this->hasMany(AdvertisingCampaign::class, 'partner_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
