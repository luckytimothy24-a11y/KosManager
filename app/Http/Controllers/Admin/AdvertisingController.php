<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingOrder;
use App\Models\AdvertisingPackage;
use App\Services\AdvertisingService;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Support\AdvertisingLabels;
use Illuminate\Http\Request;

class AdvertisingController extends Controller
{
    protected AdvertisingService $service;

    public function __construct(AdvertisingService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $kosIds = $user->assignedKos()->pluck('kos.id');

        // Admin hanya melihat kampanye kos yang ia kelola PLUS kampanye advertiser
        // pihak ketiga (kos_id NULL) yang bersifat global.
        $query = AdvertisingCampaign::with(['owner', 'kos', 'package'])
            ->where(function ($q) use ($kosIds) {
                $q->whereNull('kos_id')
                    ->orWhereHas('kos', fn ($k) => $k->whereIn('kos.id', $kosIds));
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $campaigns = $query->latest()->paginate(12)->withQueryString();

        $statuses = collect([
            'draft', 'pending_payment', 'paid', 'pending_review', 'approved',
            'active', 'completed', 'rejected', 'suspended', 'cancelled',
        ]);

        return view('admin.advertising.index', compact('campaigns', 'statuses'));
    }

    /**
     * Form pembuatan kampanye ADVERTISER PIHAK KETIGA oleh admin
     * (kos_id NULL — bersifat global, bukan untuk promosi kos tertentu).
     */
    public function create()
    {
        $this->authorize('create', [AdvertisingCampaign::class, null]);

        $packages = AdvertisingPackage::active()->orderBy('sort_order')->orderBy('price')->get();
        $placements = self::placementOptions();

        return view('admin.advertising.create', compact('packages', 'placements'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', [AdvertisingCampaign::class, null]);

        $data = $request->validate(self::thirdPartyRules());

        $result = $this->service->createThirdPartyByModerator($request->user(), $data);

        if (! $result['ok']) {
            return back()->withErrors(['package' => $result['message']])->withInput();
        }

        $campaign = $result['campaign'];

        AuditLogService::create(
            'Advertising',
            "Kampanye iklan pihak ketiga {$campaign->campaign_number} dibuat oleh moderator",
            ['campaign_id' => $campaign->id, 'package_id' => $campaign->package_id, 'placement' => $campaign->placement]
        );

        return redirect()->route('admin.advertising.show', $campaign)
            ->with('success', 'Kampanye iklan pihak ketiga berhasil dibuat.');
    }

    public function edit(AdvertisingCampaign $campaign)
    {
        $this->authorize('update', $campaign);

        $campaign->load('package');
        $packages = AdvertisingPackage::active()->orderBy('sort_order')->orderBy('price')->get();
        $placements = self::placementOptions();

        return view('admin.advertising.edit', compact('campaign', 'packages', 'placements'));
    }

    public function update(Request $request, AdvertisingCampaign $campaign)
    {
        $this->authorize('update', $campaign);

        $data = $request->validate(self::thirdPartyRules());

        $updated = $this->service->updateThirdParty($campaign, $data);

        if (! $updated) {
            return back()->with('error', 'Kampanye tidak dapat diperbarui pada status saat ini.');
        }

        AuditLogService::log('Update', 'Advertising', "Kampanye iklan pihak ketiga {$campaign->campaign_number} diperbarui", data: ['campaign_id' => $campaign->id]);

        return redirect()->route('admin.advertising.show', $campaign)
            ->with('success', 'Kampanye iklan pihak ketiga berhasil diperbarui.');
    }

    private static function placementOptions(): array
    {
        return [
            AdvertisingCampaign::PLACEMENT_HOMEPAGE => AdvertisingLabels::placementLabel(AdvertisingCampaign::PLACEMENT_HOMEPAGE),
            AdvertisingCampaign::PLACEMENT_MARKETPLACE => AdvertisingLabels::placementLabel(AdvertisingCampaign::PLACEMENT_MARKETPLACE),
            AdvertisingCampaign::PLACEMENT_DETAIL => AdvertisingLabels::placementLabel(AdvertisingCampaign::PLACEMENT_DETAIL),
            AdvertisingCampaign::PLACEMENT_NATIVE => AdvertisingLabels::placementLabel(AdvertisingCampaign::PLACEMENT_NATIVE),
        ];
    }

    private static function thirdPartyRules(): array
    {
        return [
            'package_id' => 'required|exists:advertising_packages,id',
            'advertiser_name' => 'required|string|max:120',
            'advertiser_logo' => 'nullable|url|max:255',
            'advertiser_description' => 'nullable|string|max:1000',
            'headline' => 'required|string|max:190',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|url|max:255',
            'cta_label' => 'nullable|string|max:60',
            'destination_url' => 'required|string|max:500',
            'placement' => 'nullable|in:homepage,marketplace,detail,native',
            'starts_at' => 'nullable|date|after:yesterday',
        ];
    }

    public function show(AdvertisingCampaign $campaign)
    {
        $this->authorize('view', $campaign);
        $this->authorize('review', $campaign);

        $campaign->load(['owner', 'kos', 'package', 'approver', 'orders']);

        $impressions = $campaign->totalImpressions();
        $clicks = $campaign->totalClicks();
        $conversions = (int) $campaign->events()->where('type', 'conversion')->count();
        $ctr = $impressions > 0 ? round($clicks / $impressions * 100, 2) : 0.0;
        $conversionRate = $clicks > 0 ? round($conversions / $clicks * 100, 2) : 0.0;

        return view('admin.advertising.show', compact('campaign', 'impressions', 'clicks', 'conversions', 'ctr', 'conversionRate'));
    }

    public function approve(AdvertisingCampaign $campaign)
    {
        $this->authorize('approve', $campaign);

        $approved = $this->service->approve($campaign, auth()->user());

        if (! $approved) {
            return back()->with('error', 'Kampanye tidak dapat disetujui pada status saat ini.');
        }

        try {
            NotificationService::advertisingApproved($campaign->owner_id, $campaign->kos?->name ?? $campaign->advertiser_name);
        } catch (\Exception $e) {
            \Log::warning('Advertising approve notification failed: '.$e->getMessage());
        }

        AuditLogService::approve('Advertising', "Kampanye iklan {$campaign->campaign_number} disetujui", ['campaign_id' => $campaign->id]);

        return redirect()->route('admin.advertising.index')->with('success', 'Kampanye berhasil disetujui.');
    }

    public function reject(Request $request, AdvertisingCampaign $campaign)
    {
        $this->authorize('reject', $campaign);

        $data = $request->validate(['reason' => 'required|string|min:3']);

        $rejected = $this->service->reject($campaign, $data['reason'], $request->user());

        if (! $rejected) {
            return back()->with('error', 'Kampanye tidak dapat ditolak pada status saat ini.');
        }

        try {
            NotificationService::advertisingRejected($campaign->owner_id, $campaign->kos?->name ?? $campaign->advertiser_name, $data['reason']);
        } catch (\Exception $e) {
            \Log::warning('Advertising reject notification failed: '.$e->getMessage());
        }

        AuditLogService::reject('Advertising', "Kampanye iklan {$campaign->campaign_number} ditolak: {$data['reason']}", ['campaign_id' => $campaign->id]);

        return redirect()->route('admin.advertising.index')->with('success', 'Kampanye berhasil ditolak.');
    }

    /**
     * Tandai order advertiser pihak ketiga sebagai PAID (dana diterima).
     */
    public function markThirdPartyOrderPaid(Request $request, AdvertisingOrder $order)
    {
        $this->authorize('confirmPayment', $order);

        $ok = $this->service->markThirdPartyOrderPaid($order, $request->user());

        if (! $ok) {
            return back()->with('error', 'Order tidak dapat ditandai sebagai dibayar pada status saat ini.');
        }

        AuditLogService::log('Payment', 'Advertising', "Order {$order->order_number} ditandai terbayar", data: ['order_id' => $order->id]);

        return back()->with('success', 'Order ditandai sebagai dibayar.');
    }

    public function suspend(Request $request, AdvertisingCampaign $campaign)
    {
        $this->authorize('suspend', $campaign);

        $data = $request->validate(['reason' => 'required|string|min:3']);

        $suspended = $this->service->suspend($campaign, $data['reason']);

        if (! $suspended) {
            return back()->with('error', 'Kampanye tidak dapat ditangguhkan pada status saat ini.');
        }

        try {
            NotificationService::advertisingSuspended($campaign->owner_id, $campaign->kos?->name ?? $campaign->advertiser_name, $data['reason']);
        } catch (\Exception $e) {
            \Log::warning('Advertising suspend notification failed: '.$e->getMessage());
        }

        AuditLogService::log('Suspend', 'Advertising', "Kampanye iklan {$campaign->campaign_number} ditangguhkan: {$data['reason']}", data: ['campaign_id' => $campaign->id]);

        return redirect()->route('admin.advertising.index')->with('success', 'Kampanye berhasil ditangguhkan.');
    }
}
