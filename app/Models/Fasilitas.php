<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fasilitas extends Model
{
    use HasFactory;

    protected $table = 'fasilitas';

    public const TYPE_KOS = 'kos';

    public const TYPE_KAMAR = 'kamar';

    public const CATEGORY_ROOM = 'room';

    public const CATEGORY_BATHROOM = 'bathroom';

    public const CATEGORY_COMMON = 'common';

    public const CATEGORY_PARKING = 'parking';

    public const CATEGORY_SECURITY = 'security';

    public const CATEGORY_SERVICE = 'service';

    protected $fillable = [
        'name',
        'icon',
        'type',
        'category',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function kamar()
    {
        return $this->belongsToMany(Kamar::class, 'kamar_fasilitas');
    }

    public function kos()
    {
        return $this->belongsToMany(Kos::class, 'kos_fasilitas');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function categoryLabel(): ?string
    {
        return match ($this->category) {
            self::CATEGORY_ROOM => 'Fasilitas Kamar',
            self::CATEGORY_BATHROOM => 'Kamar Mandi',
            self::CATEGORY_COMMON => 'Fasilitas Bersama',
            self::CATEGORY_PARKING => 'Parkir',
            self::CATEGORY_SECURITY => 'Keamanan',
            self::CATEGORY_SERVICE => 'Layanan',
            default => null,
        };
    }

    public function isKosFacility(): bool
    {
        return $this->type === self::TYPE_KOS;
    }
}
