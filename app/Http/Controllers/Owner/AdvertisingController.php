<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingPackage;
use App\Services\AdvertisingAnalytics;
use App\Services\AdvertisingService;
use App\Services\AuditLogService;
use App\Support\AdvertisingLabels;
use Illuminate\Http\Request;

class AdvertisingController extends Controller
{
    protected AdvertisingService $service;

    protected AdvertisingAnalytics $analytics;

    public function __construct(AdvertisingService $service, AdvertisingAnalytics $analytics)
    {
        $this->service = $service;
        $this->analytics = $analytics;
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $stats = $this->analytics->ownerOverview($user->id);

        $recentCampaigns = AdvertisingCampaign::with(['kos', 'package'])
            ->where('owner_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        $totalCampaigns = AdvertisingCampaign::where('owner_id', $user->id)->count();

        return view('owner.advertising.dashboard', compact('stats', 'recentCampaigns', 'totalCampaigns'));
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = AdvertisingCampaign::with(['kos', 'package'])
            ->where('owner_id', $user->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $campaigns = $query->latest()->paginate(10)->withQueryString();

        return view('owner.advertising.index', compact('campaigns'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', AdvertisingCampaign::class);

        $user = $request->user();

        // Muat data kos + agregat harga untuk preview iklan (tanpa N+1).
        $kosList = $user->ownedKos()->where('status', 'active')
            ->withCount(['kamar as kamar_tersedia' => fn ($q) => $q->where('status', 'available')])
            ->withMin(['kamar as harga_mulai' => fn ($q) => $q->where('status', 'available')], 'monthly_price')
            ->withMin(['kamar as harga_harian_mulai' => fn ($q) => $q->where('status', 'available')->whereNotNull('daily_price')], 'daily_price')
            ->get();

        $packages = AdvertisingPackage::active()->orderBy('sort_order')->orderBy('price')->get();

        $packagesPreview = $packages->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'price' => $p->price,
            'duration' => $p->duration_days,
            'featured' => (bool) $p->is_featured,
            'homepage' => (bool) $p->is_homepage,
            'placement' => $p->placement,
        ])->values()->all();

        $kosPreview = $kosList->mapWithKeys(fn ($kos) => [
            $kos->id => [
                'name' => $kos->name,
                'address' => $kos->address,
                'photo' => $kos->photo ? asset('storage/'.$kos->photo) : null,
                'initial' => mb_strtoupper(trim(mb_substr((string) $kos->name, 0, 1))),
                'harga_mulai' => $kos->harga_mulai,
                'harga_harian_mulai' => $kos->harga_harian_mulai,
                'kamar_tersedia' => (int) $kos->kamar_tersedia,
                'facility_tags' => collect(preg_split('/[,;]/', (string) $kos->general_facilities))
                    ->map(fn ($f) => trim($f))->filter()->values()->take(4),
            ],
        ])->all();

        $placements = [
            AdvertisingCampaign::PLACEMENT_HOMEPAGE => AdvertisingLabels::placementLabel(AdvertisingCampaign::PLACEMENT_HOMEPAGE),
            AdvertisingCampaign::PLACEMENT_MARKETPLACE => AdvertisingLabels::placementLabel(AdvertisingCampaign::PLACEMENT_MARKETPLACE),
            AdvertisingCampaign::PLACEMENT_DETAIL => AdvertisingLabels::placementLabel(AdvertisingCampaign::PLACEMENT_DETAIL),
            AdvertisingCampaign::PLACEMENT_NATIVE => AdvertisingLabels::placementLabel(AdvertisingCampaign::PLACEMENT_NATIVE),
        ];

        return view('owner.advertising.create', compact('kosList', 'packages', 'kosPreview', 'packagesPreview', 'placements'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ad_type' => 'sometimes|in:kos,partner',
            'kos_id' => 'required_if:ad_type,kos|nullable|exists:kos,id',
            'package_id' => 'required|exists:advertising_packages,id',
            'starts_at' => 'nullable|date|after:yesterday',
            'advertiser_name' => 'required_if:ad_type,partner|nullable|string|max:120',
            'advertiser_logo' => 'nullable|url|max:255',
            'advertiser_description' => 'nullable|string|max:1000',
            'headline' => 'required_if:ad_type,partner|nullable|string|max:190',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|url|max:255',
            'cta_label' => 'nullable|string|max:60',
            'destination_url' => 'required_if:ad_type,partner|nullable|url|max:500',
            'placement' => 'nullable|in:homepage,marketplace,detail,native',
        ]);

        // Backward compatibility: tanpa ad_type dianggap promosi kos.
        $data['ad_type'] = $data['ad_type'] ?? 'kos';

        $startsAt = $data['starts_at'] ?? now()->toDateString();
        $data['starts_at'] = now()->parse($startsAt);

        if ($data['ad_type'] === 'partner') {
            $this->authorize('create', [AdvertisingCampaign::class, null]);

            $result = $this->service->createThirdParty($request->user(), $data);
            $entityLabel = 'advertiser pihak ketiga';
        } else {
            $this->authorize('create', [AdvertisingCampaign::class, (int) $data['kos_id']]);

            $result = $this->service->create($request->user(), $data);
            $entityLabel = 'kos';
        }

        if (! $result['ok']) {
            return back()->withErrors(['package' => $result['message']])->withInput();
        }

        $campaign = $result['campaign'];

        AuditLogService::create(
            'Advertising',
            "Kampanye iklan {$campaign->campaign_number} dibuat untuk {$entityLabel}",
            ['campaign_id' => $campaign->id, 'kos_id' => $campaign->kos_id, 'package_id' => $campaign->package_id]
        );

        return redirect()->route('owner.advertising.show', $campaign)
            ->with('success', 'Kampanye iklan berhasil dibuat. Silakan lakukan pembayaran.');
    }

    public function show(AdvertisingCampaign $campaign)
    {
        $this->authorize('view', $campaign);

        $campaign->load(['kos', 'package', 'orders']);

        $impressions = $campaign->totalImpressions();
        $clicks = $campaign->totalClicks();
        $conversions = (int) $campaign->events()->where('type', 'conversion')->count();
        $ctr = $impressions > 0 ? round($clicks / $impressions * 100, 2) : 0.0;
        $conversionRate = $clicks > 0 ? round($conversions / $clicks * 100, 2) : 0.0;

        return view('owner.advertising.show', compact('campaign', 'impressions', 'clicks', 'conversions', 'ctr', 'conversionRate'));
    }

    public function pay(Request $request, AdvertisingCampaign $campaign)
    {
        $this->authorize('pay', $campaign);

        $result = $this->service->pay($campaign, $request->user());

        if (! $result['ok']) {
            return back()->with('error', $result['message']);
        }

        AuditLogService::log('Payment', 'Advertising', "Pembayaran kampanye iklan {$campaign->campaign_number} diterima", data: ['campaign_id' => $campaign->id, 'order_id' => $result['order']->id]);

        return redirect()->route('owner.advertising.show', $campaign)
            ->with('success', 'Pembayaran kampanye berhasil. Kampanye sedang menunggu review.');
    }

    public function cancel(Request $request, AdvertisingCampaign $campaign)
    {
        $this->authorize('cancel', $campaign);

        $cancelled = $this->service->cancel($campaign, $request->user());

        if (! $cancelled) {
            return back()->with('error', 'Kampanye tidak dapat dibatalkan pada status saat ini.');
        }

        AuditLogService::log('Cancel', 'Advertising', "Kampanye iklan {$campaign->campaign_number} dibatalkan oleh owner", data: ['campaign_id' => $campaign->id]);

        return redirect()->route('owner.advertising.index')->with('success', 'Kampanye berhasil dibatalkan.');
    }
}
