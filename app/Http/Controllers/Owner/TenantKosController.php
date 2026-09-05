<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AdvertisingCampaign;
use App\Models\Fasilitas;
use App\Models\Favorite;
use App\Models\Kamar;
use App\Models\Kos;
use App\Services\AdvertisingService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TenantKosController extends Controller
{
    protected AdvertisingService $advertisingService;

    public function __construct(AdvertisingService $advertisingService)
    {
        $this->advertisingService = $advertisingService;
    }

    private const VALID_SORT = [
        'terbaru', 'harga_terendah', 'harga_tertinggi',
        'terbanyak_disewa', 'paling_banyak_favorit',
    ];

    public function index(Request $request)
    {
        $query = Kos::where('status', 'active')
            ->withCount(['kamar as kamar_tersedia' => fn ($q) => $q->where('status', 'available')])
            ->withMin(['kamar as harga_mulai' => fn ($q) => $q->where('status', 'available')], 'monthly_price')
            ->withMin(['kamar as harga_harian_mulai' => fn ($q) => $q->where('status', 'available')->whereNotNull('daily_price')], 'daily_price')
            ->withCount('favorites')
            ->with(['kamar' => function ($q) {
                $q->where('status', 'available')->with('fasilitas')->limit(1);
            }, 'fasilitas' => function ($q) {
                $q->active();
            }]);

        if ($request->filled('q')) {
            $search = trim(addcslashes($request->q, '%_'));
            $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('kamar.fasilitas', fn ($fq) => $fq->where('fasilitas.name', 'like', "%{$search}%")));
        }

        $selectedLocation = null;
        if ($request->filled('loc')) {
            $loc = trim($request->loc);
            $query->where('address', $loc);
            $selectedLocation = $loc;
        }

        if ($request->boolean('tersedia_only')) {
            $query->whereHas('kamar', fn ($q) => $q->where('status', 'available'));
        }

        if ($request->filled('price_min') && $request->filled('price_max')) {
            $priceMin = max(0, (int) $request->price_min);
            $priceMax = max($priceMin, (int) $request->price_max);
            $this->applyPriceRange($query, $priceMin, $priceMax);
        } elseif ($request->filled('price_min')) {
            $this->applyPriceMin($query, max(0, (int) $request->price_min));
        } elseif ($request->filled('price_max')) {
            $this->applyPriceMax($query, max(0, (int) $request->price_max));
        }

        if ($request->filled('facilities')) {
            $facilityIds = array_map('intval', $request->facilities);

            $query->where(function ($q) use ($facilityIds) {
                foreach ($facilityIds as $fid) {
                    $q->where(function ($w) use ($fid) {
                        // Kos menyediakan fasilitas level Kos (kos_fasilitas) ...
                        $w->whereHas('fasilitas', function ($fq) use ($fid) {
                            $fq->where('fasilitas.id', $fid);
                        });
                        // ... ATAU pada kamar yang tersedia (kamar_fasilitas).
                        $w->orWhereHas('kamar', function ($kq) use ($fid) {
                            $kq->where('status', 'available')
                                ->whereHas('fasilitas', function ($fq) use ($fid) {
                                    $fq->where('fasilitas.id', $fid);
                                });
                        });
                    });
                }
            });
        }

        $sort = in_array($request->get('sort'), self::VALID_SORT) ? $request->get('sort') : 'terbaru';
        match ($sort) {
            'harga_terendah' => $query->orderByRaw('COALESCE(harga_mulai, 999999999) ASC'),
            'harga_tertinggi' => $query->orderByRaw('CASE WHEN harga_mulai IS NULL THEN 1 ELSE 0 END ASC, harga_mulai DESC'),
            'terbanyak_disewa' => $query->orderByRaw('(SELECT COUNT(*) FROM bookings WHERE bookings.kos_id = kos.id) DESC'),
            'paling_banyak_favorit' => $query->orderByRaw('(SELECT COUNT(*) FROM favorites WHERE favorites.kos_id = kos.id) DESC'),
            default => $query->latest(),
        };

        $kosList = $query->paginate(9)->withQueryString();

        // Advertising untuk placement marketplace: hanya kampanye ACTIVE advertiser
        // PIHAK KETIGA (kos_id NULL). Kos yang tampil di bawah TETAP murni organik —
        // seluruh search/filter/sort/availability di atas tidak dipengaruhi iklan.
        $marketplaceAds = $this->advertisingService->partnerAds(AdvertisingCampaign::PLACEMENT_MARKETPLACE, 3);

        foreach ($marketplaceAds as $ad) {
            $this->advertisingService->trackEvent(
                $ad->id,
                'impression',
                auth()->check() ? auth()->id() : null,
                session()->getId(),
                AdvertisingCampaign::PLACEMENT_MARKETPLACE,
                true
            );
        }

        // Promosi kos owner (featured/sponsored). Hanya kos yang LOLOS filter
        // pencarian/harga/fasilitas/ketersediaan di atas yang boleh tampil —
        // promosi TIDAK pernah membongkar hasil organik (lihat audit test
        // test_marketplace_sponsored_kos_respects_price_filter).
        $promotedKosIds = (clone $query)->pluck('id');

        $promotedKos = $this->advertisingService->kosPromoCampaigns(
            AdvertisingCampaign::PLACEMENT_MARKETPLACE,
            $promotedKosIds->all(),
            4
        );

        foreach ($promotedKos as $ad) {
            $this->advertisingService->trackEvent(
                (int) $ad->id,
                'impression',
                auth()->check() ? auth()->id() : null,
                session()->getId(),
                AdvertisingCampaign::PLACEMENT_MARKETPLACE,
                true
            );
        }

        $fasilitasList = Fasilitas::active()->orderBy('name')->get();

        $favoritedIds = auth()->check() && auth()->user()->isTenant()
            ? Favorite::where('user_id', auth()->id())->pluck('kos_id')->all()
            : [];

        $facilityCategories = self::facilityCategories($fasilitasList);

        $locations = self::locationDiscovery();

        return view('tenant.kos.index', compact('kosList', 'fasilitasList', 'favoritedIds', 'facilityCategories', 'selectedLocation', 'locations', 'marketplaceAds', 'promotedKos'));
    }

    /**
     * Daftar lokasi/alamat yang dapat dijelajahi berasal dari data kos aktif.
     * Menggunakan agregasi database (groupBy) — bukan pars string/fake data.
     */
    public static function locationDiscovery(): Collection
    {
        return Kos::query()
            ->where('status', 'active')
            ->selectRaw('address, COUNT(*) as kos_count')
            ->groupBy('address')
            ->havingRaw('COUNT(*) >= 1')
            ->orderByDesc('kos_count')
            ->orderBy('address')
            ->get();
    }

    public function show(Kos $kos)
    {
        abort_unless($kos->status === 'active', 404);

        $kos->load('fasilitas')->loadCount('favorites');

        $allKamar = Kamar::where('kos_id', $kos->id)
            ->with('fasilitas')
            ->orderBy('room_number')
            ->get();

        $kamarTersedia = $allKamar->filter(fn ($k) => $k->status === 'available');

        $hargaMulai = $kamarTersedia->isNotEmpty()
            ? $kamarTersedia->min('monthly_price')
            : null;

        $favoriteCount = $kos->favorites_count;

        $isFavorited = auth()->check()
            && Favorite::where('user_id', auth()->id())->where('kos_id', $kos->id)->exists();

        // Advertising untuk halaman detail: kampanye ACTIVE PIHAK KETIGA yang
        // relevan untuk penghuni (placement detail). Tidak memengaruhi informasi
        // kos organik apa pun (kamar, harga, fasilitas, booking).
        $detailAds = $this->advertisingService->partnerAds(AdvertisingCampaign::PLACEMENT_DETAIL, 3);

        foreach ($detailAds as $ad) {
            $this->advertisingService->trackEvent(
                $ad->id,
                'impression',
                auth()->check() ? auth()->id() : null,
                session()->getId(),
                AdvertisingCampaign::PLACEMENT_DETAIL,
                true
            );
        }

        $facilityGroups = self::groupFacilities($kos, $allKamar);

        $similarKos = Kos::query()
            ->where('id', '!=', $kos->id)
            ->where('status', 'active')
            ->whereHas('kamar', fn ($q) => $q->where('status', 'available'))
            ->withCount(['kamar as kamar_tersedia' => fn ($q) => $q->where('status', 'available')])
            ->withMin(['kamar as harga_mulai' => fn ($q) => $q->where('status', 'available')], 'monthly_price')
            ->when($hargaMulai, fn ($q) => $q->orderByRaw('ABS(COALESCE(harga_mulai, 999999999) - ?)', [$hargaMulai]))
            ->orderByRaw('COALESCE(harga_mulai, 999999999)')
            ->limit(3)
            ->get();

        return view('tenant.kos.show', compact('kos', 'allKamar', 'kamarTersedia', 'hargaMulai', 'favoriteCount', 'facilityGroups', 'similarKos', 'isFavorited', 'detailAds'));
    }

    /**
     * Peta kategori master (key) -> kategori kolom yang ditampilkan di UI.
     * Mempertahankan key lama agar view detail tetap kompatibel.
     */
    private const CATEGORY_LABELS = [
        'kamar' => 'room',
        'kamar_mandi' => 'bathroom',
        'bersama' => 'common',
        'parkir_keamanan' => 'parking',
        'layanan' => 'service',
    ];

    private const CATEGORY_ICONS = [
        'kamar' => 'ri-hotel-bed-line',
        'kamar_mandi' => 'ri-drop-line',
        'bersama' => 'ri-restaurant-2-line',
        'parkir_keamanan' => 'ri-parking-line',
        'layanan' => 'ri-tools-line',
    ];

    /**
     * Kategori fallback berdasar nama (untuk record lama tanpa kolom category).
     */
    private const CATEGORY_FALLBACK = [
        'room' => ['AC', 'Kipas', 'TV', 'Kasur', 'Lemari', 'Almari', 'Meja', 'Meja Belajar', 'Kursi', 'Jendela', 'WiFi', 'Ventilasi', 'Balkon'],
        'bathroom' => ['Kamar Mandi Dalam', 'Kamar Mandi Luar', 'Shower', 'Water Heater', 'Air Panas', 'Kloset Duduk', 'Kloset Jongkok', 'Wastafel', 'Bak Mandi'],
        'common' => ['Dapur', 'Dapur Bersama', 'Ruang Makan', 'Ruang Tamu', 'Ruang Keluarga', 'Ruang Santai', 'Ruang Jemur', 'Area Jemur', 'Taman', 'Mushola', 'Dispenser', 'Kulkas', 'Mesin Cuci', 'TV Bersama', 'Teras'],
        'parking' => ['Parkir', 'Parkir Motor', 'Parkir Mobil', 'Parkir Sepeda', 'Garasi'],
        'security' => ['CCTV', 'Penjaga Kos', 'Keamanan 24 Jam', 'Security', 'Akses 24 Jam', 'Smart Lock'],
        'service' => ['Laundry', 'Cleaning Service', 'Housekeeping', 'Maintenance'],
    ];

    /**
     * Urutan kategori filter (key -> label).
     */
    private const FILTER_CATEGORIES = [
        'room' => 'Fasilitas Kamar',
        'bathroom' => 'Kamar Mandi',
        'common' => 'Fasilitas Bersama',
        'parking' => 'Parkir',
        'security' => 'Keamanan',
        'service' => 'Layanan',
        'lainnya' => 'Fasilitas Lainnya',
    ];

    /**
     * Tentukan kategori untuk sebuah fasilitas (kolom dulu, fallback nama).
     */
    private function categoryOf(?string $category, string $name): ?string
    {
        if (! empty($category) && in_array($category, ['room', 'bathroom', 'common', 'parking', 'security', 'service'], true)) {
            return $category;
        }

        foreach (self::CATEGORY_FALLBACK as $key => $names) {
            if (in_array($name, $names, true)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Kelompokkan master fasilitas menurut kategori untuk filter.
     *
     * @param  Collection<int, Fasilitas>  $list
     * @return array<string, Collection<int, Fasilitas>>
     */
    private function facilityCategories(Collection $list): array
    {
        $groups = [];

        foreach (array_keys(self::FILTER_CATEGORIES) as $key) {
            $items = $list->filter(fn ($f) => $this->categoryOf($f->category, $f->name) === $key)->values();

            if ($items->isNotEmpty()) {
                $groups[$key] = $items;
            }
        }

        return $groups;
    }

    /**
     * Kategorikan fasilitas kamar menjadi grup yang familier, hanya dari data
     * fasilitas yang benar-benar ada pada kamar di kos ini (tidak dikarang).
     *
     * @param  Collection<int, Kamar>  $kamar
     * @return array<string, Collection<int, array{name: string, icon: string|null}>>
     */
    private function groupFacilities(Kos $kos, $kamar): array
    {
        // Kumpulkan fasilitas unik lintas kamar (pakai relasi database yang nyata).
        $facilities = collect();

        // Fasilitas umum dari kolom general_facilities (string nyata di database).
        if (! empty($kos->general_facilities)) {
            $parts = preg_split('/[;,]+/', $kos->general_facilities);
            foreach ((array) $parts as $part) {
                $name = trim($part);
                if ($name !== '') {
                    $facilities->push(['name' => $name, 'icon' => null, 'category' => null]);
                }
            }
        }

        // Fasilitas umum via relasi kos_fasilitas.
        foreach ($kos->fasilitas as $f) {
            $facilities->push(['name' => $f->name, 'icon' => $f->icon, 'category' => $f->category]);
        }

        foreach ($kamar as $k) {
            foreach ($k->fasilitas as $f) {
                $facilities->push(['name' => $f->name, 'icon' => $f->icon, 'category' => $f->category]);
            }
        }
        $facilities = $facilities->unique('name')->values();

        $groupKeys = ['kamar', 'kamar_mandi', 'bersama', 'parkir_keamanan', 'layanan'];
        $assigned = [];
        $groups = [];

        foreach ($groupKeys as $key) {
            $category = self::CATEGORY_LABELS[$key];

            $list = $facilities->filter(function ($f) use ($category, &$assigned) {
                if (in_array($f['name'], $assigned, true)) {
                    return false;
                }

                $matches = $this->categoryOf($f['category'], $f['name']) === $category;
                if (! $matches) {
                    return false;
                }

                $assigned[] = $f['name'];

                return true;
            })->values();

            // Fasilitas keamanan dimasukkan ke grup "Parkir & Keamanan" juga.
            if ($key === 'parkir_keamanan') {
                $securityItems = $facilities->filter(function ($f) use (&$assigned) {
                    if (in_array($f['name'], $assigned, true)) {
                        return false;
                    }
                    $cat = $this->categoryOf($f['category'], $f['name']);
                    if ($cat !== 'parking' && $cat !== 'security') {
                        return false;
                    }
                    $assigned[] = $f['name'];

                    return true;
                })->values();

                if ($securityItems->isNotEmpty()) {
                    $list = $list->concat($securityItems)->unique('name')->values();
                }
            }

            if ($list->isNotEmpty()) {
                $groups[$key] = $list;
            }
        }

        // Sisa fasilitas yang tidak punya kategori -> taruh di "Fasilitas Lainnya" bila ada.
        $leftover = $facilities->filter(fn ($f) => ! in_array($f['name'], $assigned, true))->values();
        if ($leftover->isNotEmpty()) {
            $groups['lainnya'] = $leftover;
        }

        return $groups;
    }

    /**
     * Batasi kos yang memiliki kamar tersedia dengan harga bulanan dalam rentang,
     * ATAU kos "harian saja" (tanpa harga bulanan) yang punya kamar dengan harga
     * harian dalam rentang. Kos yang memiliki harga bulanan dinilai dari harga
     * bulanannya saja agar filter tetap bermakna.
     */
    private function applyPriceRange($query, int $priceMin, int $priceMax): void
    {
        $query->where(function ($w) use ($priceMin, $priceMax) {
            $w->whereHas('kamar', fn ($q) => $q->where('status', 'available')
                ->whereBetween('monthly_price', [$priceMin, $priceMax]))
                ->orWhere(function ($daily) use ($priceMin, $priceMax) {
                    $daily->whereDoesntHave('kamar', fn ($q) => $q->where('status', 'available')->whereNotNull('monthly_price'))
                        ->whereHas('kamar', fn ($q) => $q->where('status', 'available')->whereNotNull('daily_price')
                            ->whereBetween('daily_price', [$priceMin, $priceMax]));
                });
        });
    }

    private function applyPriceMin($query, int $priceMin): void
    {
        $query->where(function ($w) use ($priceMin) {
            $w->whereHas('kamar', fn ($q) => $q->where('status', 'available')
                ->where('monthly_price', '>=', $priceMin))
                ->orWhere(function ($daily) use ($priceMin) {
                    $daily->whereDoesntHave('kamar', fn ($q) => $q->where('status', 'available')->whereNotNull('monthly_price'))
                        ->whereHas('kamar', fn ($q) => $q->where('status', 'available')->whereNotNull('daily_price')
                            ->where('daily_price', '>=', $priceMin));
                });
        });
    }

    private function applyPriceMax($query, int $priceMax): void
    {
        $query->where(function ($w) use ($priceMax) {
            $w->whereHas('kamar', fn ($q) => $q->where('status', 'available')
                ->where('monthly_price', '<=', $priceMax))
                ->orWhere(function ($daily) use ($priceMax) {
                    $daily->whereDoesntHave('kamar', fn ($q) => $q->where('status', 'available')->whereNotNull('monthly_price'))
                        ->whereHas('kamar', fn ($q) => $q->where('status', 'available')->whereNotNull('daily_price')
                            ->where('daily_price', '<=', $priceMax));
                });
        });
    }
}
