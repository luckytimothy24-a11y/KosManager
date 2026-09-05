<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisingPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'price',
        'duration_days',
        'is_featured',
        'is_sponsored',
        'is_homepage',
        'placement',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_days' => 'integer',
        'is_featured' => 'boolean',
        'is_sponsored' => 'boolean',
        'is_homepage' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function campaigns()
    {
        return $this->hasMany(AdvertisingCampaign::class, 'package_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function hasAnyPromotion(): bool
    {
        return $this->is_featured || $this->is_sponsored || $this->is_homepage;
    }

    /**
     * Placement andalan paket (homepage | marketplace | detail | native).
     * Paket lama (promosi kos) tidak memiliki placement.
     */
    public function isThirdParty(): bool
    {
        return $this->placement !== null
            && in_array($this->placement, AdvertisingCampaign::PLACEMENTS, true);
    }
}
