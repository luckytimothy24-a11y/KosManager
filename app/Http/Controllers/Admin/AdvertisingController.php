<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdvertisingCampaign;
use App\Services\AdvertisingService;
use App\Services\AuditLogService;
use App\Services\NotificationService;
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

        $rejected = $this->service->reject($campaign, $data['reason']);

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
