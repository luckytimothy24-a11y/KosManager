<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topik extends Model
{
    use HasFactory;

    protected $table = 'topik';

    public const CATEGORY_KAMAR = 'kamar';

    public const CATEGORY_FASILITAS = 'fasilitas';

    public const CATEGORY_TAGIHAN = 'tagihan';

    public const CATEGORY_PEMBAYARAN = 'pembayaran';

    public const CATEGORY_BOOKING = 'booking';

    public const CATEGORY_KONTRAK = 'kontrak';

    public const CATEGORY_LAINNYA = 'lainnya';

    public const CATEGORIES = [
        self::CATEGORY_KAMAR,
        self::CATEGORY_FASILITAS,
        self::CATEGORY_TAGIHAN,
        self::CATEGORY_PEMBAYARAN,
        self::CATEGORY_BOOKING,
        self::CATEGORY_KONTRAK,
        self::CATEGORY_LAINNYA,
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function categoryLabel(): ?string
    {
        return match ($this->category) {
            self::CATEGORY_KAMAR => 'Kamar',
            self::CATEGORY_FASILITAS => 'Fasilitas',
            self::CATEGORY_TAGIHAN => 'Tagihan',
            self::CATEGORY_PEMBAYARAN => 'Pembayaran',
            self::CATEGORY_BOOKING => 'Booking',
            self::CATEGORY_KONTRAK => 'Kontrak',
            self::CATEGORY_LAINNYA => 'Lainnya',
            default => null,
        };
    }
}
