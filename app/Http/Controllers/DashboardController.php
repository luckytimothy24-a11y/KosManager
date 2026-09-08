<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Owner\TenantKosController;
use App\Models\AdvertisingCampaign;
use App\Models\Booking;
use App\Models\CheckIn;
use App\Models\CheckOut;
use App\Models\Fasilitas;
use App\Models\Favorite;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\AdvertisingService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private readonly AdvertisingService $advertisingService) {}

    public function __invoke(Request $request)
    {
        $user = $request->user();

        return match ($user->role) {
            'super_admin' => $this->superAdmin($user),
            'admin' => $this->admin($user),
            'owner' => $this->owner($user),
            'tenant' => $this->tenant($user),
            default => redirect()->route('login'),
        };
    }

    private function superAdmin($user)
    {
        $stats = [
            'total_users' => User::count(),
            'total_owners' => User::where('role', 'owner')->count(),
            'total_tenants' => User::where('role', 'tenant')->count(),
            'total_kos' => Kos::count(),
            'total_kamar' => Kamar::count(),
            'total_kamar_available' => Kamar::where('status', 'available')->count(),
            'total_kamar_occupied' => Kamar::where('status', 'occupied')->count(),
            'total_bookings' => Booking::count(),
            'total_active_bookings' => Booking::needsCheckin()->count(),
            'total_penghunis' => Penghuni::where('status', 'active')->count(),
            'total_revenue' => Pembayaran::where('verification_status', 'approved')->sum('amount'),
        ];

        $start = now()->subMonths(5)->startOfMonth();
        $end = now()->endOfMonth();

        $revenueMonthExpr = self::monthKeyExpr('payment_date');
        $monthlyRevenue = Pembayaran::where('verification_status', 'approved')
            ->whereBetween('payment_date', [$start, $end])
            ->selectRaw("{$revenueMonthExpr} as month, SUM(amount) as total")
            ->groupBy('month')
            ->pluck('total', 'month');
        $revenueChart = self::monthlySeries($monthlyRevenue);

        $bookingMonthExpr = self::monthKeyExpr('booking_date');
        $monthlyBookings = Booking::whereBetween('booking_date', [$start, $end])
            ->selectRaw("{$bookingMonthExpr} as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');
        $bookingChart = self::monthlySeries($monthlyBookings);

        return view('dashboard.super-admin', compact('stats', 'user', 'revenueChart', 'bookingChart'));
    }

    private function admin($user)
    {
        $kosIds = $user->assignedKos()->pluck('kos.id');

        $stats = [
            'pending_bookings' => Booking::needsCheckin()
                ->whereIn('kos_id', $kosIds)->count(),
            'pending_payments' => Pembayaran::where('verification_status', 'pending')
                ->whereHas('penghuni', fn ($q) => $q->whereIn('kos_id', $kosIds))->count(),
            'checkin_today' => CheckIn::whereDate('check_in_date', now()->toDateString())
                ->whereHas('kamar', fn ($q) => $q->whereIn('kos_id', $kosIds))->count(),
            'checkout_today' => CheckOut::whereDate('request_date', now()->toDateString())
                ->whereHas('kamar', fn ($q) => $q->whereIn('kos_id', $kosIds))->count(),
            'kamar_available' => Kamar::where('status', 'available')
                ->whereIn('kos_id', $kosIds)->count(),
            'kamar_occupied' => Kamar::where('status', 'occupied')
                ->whereIn('kos_id', $kosIds)->count(),
            'tagihan_overdue' => Tagihan::where('status', 'overdue')
                ->whereHas('kamar', fn ($q) => $q->whereIn('kos_id', $kosIds))->count(),
            'total_penghunis' => Penghuni::where('status', 'active')
                ->whereIn('kos_id', $kosIds)->count(),
        ];

        return view('dashboard.admin', compact('stats', 'user'));
    }

    private function owner($user)
    {
        $stats = [
            'total_kos' => Kos::where('owner_id', $user->id)->count(),
            'total_kamar' => Kamar::whereHas('kos', fn ($q) => $q->where('owner_id', $user->id))->count(),
            'total_kamar_available' => Kamar::where('status', 'available')->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id))->count(),
            'total_kamar_occupied' => Kamar::where('status', 'occupied')->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id))->count(),
            'total_kamar_maintenance' => Kamar::where('status', 'maintenance')->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id))->count(),
            'needs_checkin' => Booking::needsCheckin()
                ->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id))->count(),
            'pending_payments' => Pembayaran::where('verification_status', 'pending')->whereHas('penghuni.kos', fn ($q) => $q->where('owner_id', $user->id))->count(),
            'total_penghunis' => Penghuni::whereHas('kos', fn ($q) => $q->where('owner_id', $user->id))->where('status', 'active')->count(),
            'total_revenue' => Pembayaran::where('verification_status', 'approved')->whereHas('penghuni.kos', fn ($q) => $q->where('owner_id', $user->id))->sum('amount'),
            'tagihan_outstanding' => Tagihan::payable()->whereHas('kamar.kos', fn ($q) => $q->where('owner_id', $user->id))->count(),
            'tagihan_overdue' => Tagihan::where('status', 'overdue')->whereHas('kamar.kos', fn ($q) => $q->where('owner_id', $user->id))->count(),
        ];

        $monthExpr = self::monthKeyExpr('payment_date');
        $ownerMonthlyRevenue = Pembayaran::where('verification_status', 'approved')
            ->whereHas('penghuni.kos', fn ($q) => $q->where('owner_id', $user->id))
            ->whereBetween('payment_date', [now()->subMonths(5)->startOfMonth(), now()->endOfMonth()])
            ->selectRaw("{$monthExpr} as month, SUM(amount) as total")
            ->groupBy('month')
            ->pluck('total', 'month');
        $revenueChart = self::monthlySeries($ownerMonthlyRevenue);

        $totalKamar = max(1, $stats['total_kamar']);
        $stats['occupancy_percent'] = (int) round(($stats['total_kamar_occupied'] / $totalKamar) * 100);

        return view('dashboard.owner', compact('stats', 'user', 'revenueChart'));
    }

    private function tenant($user)
    {
        $penghuni = Penghuni::where('user_id', $user->id)->where('status', 'active')
            ->with(['kos', 'kamar'])->first();
        $stats = [];
        $pendingCheckout = false;

        if ($penghuni) {
            $pendingCheckout = $penghuni->checkOuts()->where('status', 'pending')->exists();
            $kontrak = $penghuni->kontraks()->where('status', 'active')->first();

            $nearestDue = $penghuni->tagihans()
                ->outstanding()
                ->orderBy('due_date')
                ->value('due_date');

            $lastPayment = $penghuni->pembayarans()
                ->where('verification_status', 'approved')
                ->latest('payment_date')
                ->first();

            $stats = [
                'kos_name' => $penghuni->kos->name,
                'kamar_number' => $penghuni->kamar->room_number,
                'kontrak_status' => $kontrak?->status ?? '-',
                'kontrak_end' => $kontrak?->end_date,
                'tagihan_pending' => $penghuni->tagihans()->payable()->count(),
                'tagihan_overdue' => $penghuni->tagihans()->where('status', 'overdue')->count(),
                'tagihan_pending_verification' => $penghuni->tagihans()->where('status', 'pending_verification')->count(),
                'total_belum_dibayar' => (float) $penghuni->tagihans()->payable()->sum('total'),
                'nearest_due' => $nearestDue,
                'last_payment_amount' => $lastPayment?->amount,
                'last_payment_date' => $lastPayment?->payment_date,
            ];
        }

        $favoritedIds = Favorite::where('user_id', $user->id)->pluck('kos_id')->all();
        $favoriteKosIds = array_slice($favoritedIds, 0, 4);

        $favoriteCount = Favorite::where('user_id', $user->id)
            ->whereHas('kos', fn ($q) => $q->where('status', 'active'))
            ->count();

        // Skor popularitas dari metrik nyata (booking + favorite) sebagai urutan rekomendasi.
        // Tidak ada skor palsu: cukup gunakan count yang benar-benar tersedia di database.
        $recommendations = $this->scopedKos()
            ->withCount('bookings')
            ->orderByDesc('bookings_count')
            ->orderByDesc('favorites_count')
            ->latest()
            ->limit(6)
            ->get();

        $shownIds = $recommendations->pluck('id')->all();

        // Kos terbaru — jangan mengulang daftar yang sudah tampil pada rekomendasi.
        // Tampilkan hanya jika ada minimal 2 kos unik yang belum ditampilkan sebelumnya,
        // agar section tidak terasa kosong/repetitif saat data terbatas.
        $latestKos = $this->scopedKos()
            ->whereNotIn('id', $shownIds)
            ->latest()
            ->limit(6)
            ->get();

        if ($latestKos->count() < 2) {
            $latestKos = collect();
        }

        $shownIds = array_merge($shownIds, $latestKos->pluck('id')->all());

        // Discovery berdasarkan fasilitas nyata (kos + kamar). Jalankan lebih dulu
        // agar kos ber-fasilitas tidak "ditebas" oleh budget discovery berikutnya.
        $facilityDiscovery = $this->facilityDiscovery($shownIds);

        $shownIds = array_merge($shownIds, collect($facilityDiscovery)->flatMap(fn ($s) => $s['kos']->pluck('id'))->all());

        // Discovery berdasarkan budget (harga nyata dari kamar tersedia).
        $budgetDiscovery = $this->budgetDiscovery($shownIds);

        $favoriteKos = empty($favoriteKosIds)
            ? collect()
            : Kos::whereIn('id', $favoriteKosIds)
                ->where('status', 'active')
                ->withCount(['kamar as kamar_tersedia' => fn ($q) => $q->where('status', 'available')])
                ->withMin(['kamar as harga_mulai' => fn ($q) => $q->where('status', 'available')], 'monthly_price')
                ->withCount('favorites')
                ->with(['kamar' => fn ($q) => $q->where('status', 'available')->with('fasilitas')->limit(1)])
                ->get()
                ->sortBy(fn ($k) => array_search($k->id, $favoriteKosIds))
                ->values();

        $locations = TenantKosController::locationDiscovery();

        // Advertising untuk audience (tenant): hanya menampilkan kampanye ACTIVE
        // advertiser PIHAK KETIGA pada placement homepage. Kos tetap murni organik —
        // iklan sama sekali tidak memengaruhi daftar rekomendasi/next mari kita.
        $partnerAds = collect();
        $kosPromosHome = collect();
        $tenantDashboardAds = collect();
        if (auth()->check() && auth()->user()->isTenant()) {
            $partnerAds = $this->advertisingService->partnerAds(AdvertisingCampaign::PLACEMENT_HOMEPAGE, 4);

            foreach ($partnerAds as $ad) {
                $this->advertisingService->trackEvent(
                    (int) $ad->id,
                    'impression',
                    auth()->id(),
                    session()->getId(),
                    AdvertisingCampaign::PLACEMENT_HOMEPAGE,
                    true
                );
            }

            // Promosi kos owner ber-placement homepage (is_homepage, kos_id TIDAK
            // NULL) di beranda tenant — pemilik kos yang membayar mendapat ruang
            // tayang yang JELAS berlabel, tanpa menyentuh rekomendasi organik.
            $kosPromosHome = $this->advertisingService->kosPromoCampaigns(
                AdvertisingCampaign::PLACEMENT_HOMEPAGE,
                [],
                2
            );

            foreach ($kosPromosHome as $ad) {
                $this->advertisingService->trackEvent(
                    (int) $ad->id,
                    'impression',
                    auth()->id(),
                    session()->getId(),
                    AdvertisingCampaign::PLACEMENT_HOMEPAGE,
                    true
                );
            }

            // Placement khusus DASHBOARD TENANT ($tenantDashboardAds) — slot iklan
            // partner yang berbeda dari carousel homepage, ditampilkan sebagai kartu
            // ringkas di bawah.Kontennya JELAS terpisah dari kos organik.
            $tenantDashboardAds = $this->advertisingService->partnerAds(
                AdvertisingCampaign::PLACEMENT_TENANT_DASHBOARD,
                3
            );

            foreach ($tenantDashboardAds as $ad) {
                $this->advertisingService->trackEvent(
                    (int) $ad->id,
                    'impression',
                    auth()->id(),
                    session()->getId(),
                    AdvertisingCampaign::PLACEMENT_TENANT_DASHBOARD,
                    true
                );
            }
        }

        return view('dashboard.tenant', compact(
            'stats',
            'user',
            'penghuni',
            'pendingCheckout',
            'recommendations',
            'latestKos',
            'budgetDiscovery',
            'facilityDiscovery',
            'favoriteKos',
            'favoriteKosIds',
            'favoriteCount',
            'favoritedIds',
            'locations',
            'partnerAds',
            'kosPromosHome',
            'tenantDashboardAds'
        ));
    }

    /**
     * Query dasar kos aktif dengan kamar tersedia beserta agregat yang dibutuhkan card.
     */
    private function scopedKos()
    {
        return Kos::where('status', 'active')
            ->whereHas('kamar', fn ($q) => $q->where('status', 'available'))
            ->withCount(['kamar as kamar_tersedia' => fn ($q) => $q->where('status', 'available')])
            ->withMin(['kamar as harga_mulai' => fn ($q) => $q->where('status', 'available')], 'monthly_price')
            ->withCount('favorites')
            ->with(['kamar' => fn ($q) => $q->where('status', 'available')->with('fasilitas')->limit(1)])
            ->with('fasilitas');
    }

    /**
     * Discovery berbasis harga nyata (kamar tersedia). Bagilah data menjadi 2
     * kelompok sesuai distribusi aktual, hanya jika data cukup & bervariasi.
     *
     * @return array<int, array{key: string, title: string, subtitle: string, kos: Collection}>
     */
    private function budgetDiscovery(array $excludedIds): array
    {
        $rows = DB::table('kamar')
            ->join('kos', 'kos.id', '=', 'kamar.kos_id')
            ->where('kos.status', 'active')
            ->where('kamar.status', 'available')
            ->whereNotIn('kamar.kos_id', $excludedIds)
            ->selectRaw('kamar.kos_id, min(kamar.monthly_price) as min_price')
            ->groupBy('kamar.kos_id')
            ->orderBy('min_price')
            ->get();

        if ($rows->count() < 2) {
            return [];
        }

        $prices = $rows->pluck('min_price')->map(fn ($p) => (float) $p)->sort()->values();
        $medianIndex = intdiv($prices->count(), 2);
        $median = ($prices->count() % 2 === 0)
            ? ($prices[$medianIndex - 1] + $prices[$medianIndex]) / 2
            : $prices[$medianIndex];

        $lowGroup = $rows->filter(fn ($r) => (float) $r->min_price < $median);
        $highGroup = $rows->filter(fn ($r) => (float) $r->min_price >= $median);

        // Jika hampir semua kos berada di satu range, jangan buat section kosong ganda.
        if ($lowGroup->count() === 0 || $highGroup->count() === 0) {
            return [];
        }

        $sections = [];

        $lowKos = $this->loadDiscoveryKos($lowGroup->pluck('kos_id'));
        if ($lowKos->isNotEmpty()) {
            $sections[] = [
                'key' => 'hemat',
                'title' => 'Kos Hemat',
                'subtitle' => 'Pilihan terjangkau di bawah harga median tersedia (Rp '.number_format($median, 0, ',', '.').').',
                'kos' => $lowKos,
            ];
        }

        $highKos = $this->loadDiscoveryKos($highGroup->pluck('kos_id'));
        if ($highKos->isNotEmpty()) {
            $sections[] = [
                'key' => 'menengah',
                'title' => 'Kos Menengah',
                'subtitle' => 'Pilihan dengan harga mulai dari Rp '.number_format($median, 0, ',', '.').'.',
                'kos' => $highKos,
            ];
        }

        return $sections;
    }

    /**
     * Discovery berbasis fasilitas nyata. Pilih fasilitas yang benar-benar
     * digunakan (kos atau kamar) dan hanya tampilkan yang memiliki hasil.
     *
     * @return array<int, array{key: string, title: string, subtitle: string, kos: Collection, facility: string}>
     */
    private function facilityDiscovery(array $excludedIds): array
    {
        $interests = ['AC', 'WiFi', 'Kamar Mandi Dalam'];

        $candidates = [];
        foreach ($interests as $name) {
            $f = Fasilitas::where('name', $name)->first();
            if (! $f) {
                continue;
            }

            $kosIds = Kos::where('status', 'active')
                ->whereHas('kamar', fn ($q) => $q->where('status', 'available'))
                ->where(function ($w) use ($f) {
                    $w->whereHas('fasilitas', fn ($q) => $q->whereKey($f->id))
                        ->orWhereHas('kamar.fasilitas', fn ($q) => $q->whereKey($f->id));
                })
                ->whereNotIn('id', $excludedIds)
                ->pluck('kos.id');

            if ($kosIds->isNotEmpty()) {
                $kos = $this->loadDiscoveryKos($kosIds);
                if ($kos->isNotEmpty()) {
                    $candidates[] = [
                        'key' => strtolower(str_replace(' ', '-', $name)),
                        'title' => "Kos dengan {$name}",
                        'subtitle' => "Fasilitas {$name} tersedia.",
                        'facility' => $name,
                        'id' => $f->id,
                        'kos' => $kos,
                    ];
                }
            }
        }

        return array_slice($candidates, 0, 2);
    }

    /**
     * Muat kos untuk section discovery lengkap dengan agregat card, urut per ID.
     */
    private function loadDiscoveryKos($ids): Collection
    {
        $ids = collect($ids)->filter()->unique()->values()->all();
        if (empty($ids)) {
            return collect();
        }

        $kos = $this->scopedKos()
            ->whereIn('id', $ids)
            ->limit(4)
            ->get();

        return $kos->sortBy(fn ($k) => array_search($k->id, $ids))->values();
    }

    /**
     * Susun seri 6 bulan terakhir (label singkat + nilai, urut kronologis).
     */
    private static function monthKeyExpr(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'mysql' => "DATE_FORMAT({$column}, '%Y-%m')",
            'pgsql' => "TO_CHAR({$column}::date, 'YYYY-MM')",
            default => "strftime('%Y-%m', {$column})",
        };
    }

    private static function monthlySeries($grouped): array
    {
        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->translatedFormat('M');
            $value = $grouped[$key] ?? 0;
            $values[] = is_numeric($value) ? (int) $value : 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
