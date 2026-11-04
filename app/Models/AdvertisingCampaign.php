<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvertisingCampaign extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    public const STATUS_PAID = 'paid';

    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_CANCELLED = 'cancelled';

    public const PLACEMENT_HOMEPAGE = 'homepage';

    public const PLACEMENT_MARKETPLACE = 'marketplace';

    public const PLACEMENT_DETAIL = 'detail';

    public const PLACEMENT_NATIVE = 'native';

    public const PLACEMENT_TENANT_DASHBOARD = 'tenant_dashboard';

    public const PLACEMENTS = [
        self::PLACEMENT_HOMEPAGE,
        self::PLACEMENT_MARKETPLACE,
        self::PLACEMENT_DETAIL,
        self::PLACEMENT_NATIVE,
        self::PLACEMENT_TENANT_DASHBOARD,
    ];

    public const VISUAL_THEME_SHOPPING = 'shopping';

    public const VISUAL_THEME_PAYMENT = 'payment';

    public const VISUAL_THEME_WALLET = 'wallet';

    public const VISUAL_THEME_GENERIC = 'generic';

    public const VISUAL_THEMES = [
        self::VISUAL_THEME_SHOPPING,
        self::VISUAL_THEME_PAYMENT,
        self::VISUAL_THEME_WALLET,
        self::VISUAL_THEME_GENERIC,
    ];

    protected $table = 'advertising_campaigns';

    protected $fillable = [
        'campaign_number',
        'owner_id',
        'kos_id',
        'partner_id',
        'package_id',
        'status',
        'starts_at',
        'ends_at',
        'budget',
        'is_featured',
        'is_sponsored',
        'is_homepage',
        'advertiser_name',
        'advertiser_logo',
        'advertiser_description',
        'headline',
        'description',
        'highlight_text',
        'visual_theme',
        'image',
        'cta_label',
        'destination_url',
        'placement',
        'contract_reference',
        'campaign_code',
        'monetization_type',
        'target_audience',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'suspension_reason',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'budget' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_sponsored' => 'boolean',
        'is_homepage' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function kos()
    {
        return $this->belongsTo(Kos::class);
    }

    public function package()
    {
        return $this->belongsTo(AdvertisingPackage::class, 'package_id');
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function orders()
    {
        return $this->hasMany(AdvertisingOrder::class, 'campaign_id');
    }

    public function events()
    {
        return $this->hasMany(AdvertisingEvent::class, 'campaign_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isLive(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        $now = now();

        return $this->starts_at && $this->ends_at
            && $now->between($this->starts_at, $this->ends_at);
    }

    /**
     * Campaign advertiser pihak ketiga (tidak terkait kos tertentu).
     * Discriminator utama: kos_id KOSONG.
     */
    public function isThirdParty(): bool
    {
        return $this->kos_id === null;
    }

    /**
     * Nama yang ditampilkan: brand advertiser pihak ketiga, atau nama kos
     * untuk campaign promosi kos owner (kompatibilitas data lama).
     */
    public function displayLabel(): string
    {
        if ($this->isThirdParty()) {
            return $this->advertiser_name ?: 'Partner';
        }

        return $this->kos && $this->kos->exists
            ? $this->kos->name
            : 'Kos';
    }

    public function displayHeadline(): string
    {
        return $this->headline ?: ($this->isThirdParty() ? 'Penawaran khusus untuk penghuni kos' : $this->displayLabel());
    }

    public function displayDescription(): string
    {
        if ($this->isThirdParty()) {
            return $this->advertiser_description ?: ($this->description ?: 'Temukan penawaran terbaik khusus penghuni kos dan mahasiswa.');
        }

        return $this->advertiser_description
            ?: ($this->description
                ?: ($this->kos && $this->kos->exists
                    ? 'Segera isi kamar kos ini sebelum kehabisan.'
                    : 'Promosi kos tersedia.'));
    }

    /**
     * Teks penawaran besar di area visual iklan.
     *
     * Diisi advertiser lewat kolom opsional highlight_text. Bila kosong,
     * area visual hanya menampilkan ilustrasi tanpa teks besar. — TIDAK pernah mengarang angka diskon.
     */
    public function displayHighlight(): string
    {
        return trim((string) $this->highlight_text);
    }

    /**
     * Tema ilustrasi kartu: nilai eksplisit visual_theme, atau deteksi dari
     * slug/nama mitra. Selalu mengembalikan salah satu dari VISUAL_THEMES.
     */
    public function visualTheme(): string
    {
        $theme = mb_strtolower(trim((string) $this->visual_theme));

        if (in_array($theme, self::VISUAL_THEMES, true)) {
            return $theme;
        }

        $haystack = mb_strtolower(implode(' ', array_filter([
            $this->partner?->slug,
            $this->partner?->name,
            $this->advertiser_name,
        ])));

        if (str_contains($haystack, 'shopee')) {
            return self::VISUAL_THEME_SHOPPING;
        }

        if (str_contains($haystack, 'dana')) {
            return self::VISUAL_THEME_PAYMENT;
        }

        if (str_contains($haystack, 'gopay') || str_contains($haystack, 'go-pay') || str_contains($haystack, 'go pay')) {
            return self::VISUAL_THEME_WALLET;
        }

        return self::VISUAL_THEME_GENERIC;
    }

    /**
     * Sisa hari menuju akhir tayang (0 = hari ini). Null bila tidak ada data.
     * Digunakan hanya untuk badge urgensi berbasis data nyata.
     */
    public function remainingDays(): ?int
    {
        if (! $this->ends_at) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->ends_at->copy()->startOfDay(), false);
    }


    public function displayCta(): string
    {
        return $this->cta_label ?: ($this->isThirdParty() ? 'Lihat Penawaran' : 'Lihat Detail');
    }

    public function displayImage(): ?string
    {
        return $this->image ?: $this->advertiser_logo;
    }

    /**
     * Kampanye monetisasi BERMITRA (terhubung ke entitas Partner terdaftar).
     * Tidak semua kampanye pihak ketiga harus terkait partner — namun hampir
     * semua kampanye B2B (DANA/Shopee/GoPay) terpaut ke Partner.
     */
    public function isPartnerLinked(): bool
    {
        return $this->partner_id !== null;
    }

    /**
     * Nama partner terkait (entitas terdaftar) atau fallback ke nama advertiser.
     */
    public function partnerLabel(): ?string
    {
        if ($this->isPartnerLinked() && $this->partner && $this->partner->exists) {
            return $this->partner->name;
        }

        return $this->advertiser_name;
    }

    public function canBePaid(): bool
    {
        return $this->status === self::STATUS_PENDING_PAYMENT;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_PAYMENT,
            self::STATUS_PAID,
            self::STATUS_PENDING_REVIEW,
        ], true);
    }

    public function scopeActiveOrLive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function impressions()
    {
        return $this->hasMany(AdvertisingEvent::class, 'campaign_id')->where('type', 'impression');
    }

    public function clicks()
    {
        return $this->hasMany(AdvertisingEvent::class, 'campaign_id')->where('type', 'click');
    }

    public function totalImpressions(): int
    {
        return (int) $this->events()->where('type', 'impression')->count();
    }

    public function totalClicks(): int
    {
        return (int) $this->events()->where('type', 'click')->count();
    }

    public function ctr(): ?float
    {
        $impressions = $this->totalImpressions();
        $clicks = $this->totalClicks();

        if ($impressions <= 0) {
            return null;
        }

        return round($clicks / $impressions * 100, 2);
    }
}
