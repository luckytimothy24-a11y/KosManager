<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingOrder;
use App\Models\AdvertisingPackage;
use App\Models\User;
use App\Services\AdvertisingAnalytics;
use App\Services\AdvertisingService;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Support\AdvertisingLabels;
use Illuminate\Http\Request;

class AdvertisingCampaignController extends Controller
{
    protected AdvertisingService $service;

    protected AdvertisingAnalytics $analytics;

    public function __construct(AdvertisingService $service, AdvertisingAnalytics $analytics)
    {
        $this->service = $service;
        $this->analytics = $analytics;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', AdvertisingCampaign::class);

        $query = AdvertisingCampaign::with(['owner', 'kos', 'package']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        if ($request->filled('q')) {
            $search = addcslashes($request->q, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('campaign_number', 'like', '%'.$search.'%')
                    ->orWhereHas('kos', fn ($kq) => $kq->where('name', 'like', '%'.$search.'%')
                        ->orWhere('address', 'like', '%'.$search.'%'));
            });
        }

        $campaigns = $query->latest()->paginate(12)->withQueryString();
        $owners = User::where('role', 'owner')->orderBy('name')->get();
        $statuses = collect([
            'draft', 'pending_payment', 'paid', 'pending_review', 'approved',
            'active', 'completed', 'rejected', 'suspended', 'cancelled',
        ]);

        return view('super-admin.advertising.campaign.index', compact('campaigns', 'owners', 'statuses'));
    }

    public function show(AdvertisingCampaign $campaign)
    {
        $this->authorize('view', $campaign);

        $campaign->load(['owner', 'kos', 'package', 'approver', 'orders']);

        $impressions = $campaign->totalImpressions();
        $clicks = $campaign->totalClicks();
        $conversions = (int) $campaign->events()->where('type', 'conversion')->count();
        $ctr = $impressions > 0 ? round($clicks / $impressions * 100, 2) : 0.0;
        $conversionRate = $clicks > 0 ? round($conversions / $clicks * 100, 2) : 0.0;

        return view('super-admin.advertising.campaign.show', compact('campaign', 'impressions', 'clicks', 'conversions', 'ctr', 'conversionRate'));
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

        return redirect()->route(auth()->user()->isAdmin() ? 'admin.advertising.index' : 'super-admin.advertising.campaigns.index')
            ->with('success', 'Kampanye berhasil disetujui.');
    }

    public function reject(Request $request, AdvertisingCampaign $campaign)
    {
        $this->authorize('reject', $campaign);

        $data = $request->validate([
            'reason' => 'required|string|min:3',
        ]);

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

        return redirect()->route(auth()->user()->isAdmin() ? 'admin.advertising.index' : 'super-admin.advertising.campaigns.index')
            ->with('success', 'Kampanye berhasil ditolak.');
    }

    public function suspend(Request $request, AdvertisingCampaign $campaign)
    {
        $this->authorize('suspend', $campaign);

        $data = $request->validate([
            'reason' => 'required|string|min:3',
        ]);

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

        return redirect()->route(auth()->user()->isAdmin() ? 'admin.advertising.index' : 'super-admin.advertising.campaigns.index')
            ->with('success', 'Kampanye berhasil ditangguhkan.');
    }

    public function revenue(Request $request)
    {
        $this->authorize('viewAny', AdvertisingCampaign::class);

        $query = AdvertisingOrder::with(['campaign.owner', 'campaign.kos', 'campaign.package'])
            ->where('status', 'paid');

        if ($request->filled('start_date')) {
            $query->whereDate('paid_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('paid_at', '<=', $request->end_date);
        }
        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }
        if ($request->filled('package_id')) {
            $query->whereHas('campaign', fn ($q) => $q->where('package_id', $request->package_id));
        }
        if ($request->filled('campaign_status')) {
            $query->whereHas('campaign', fn ($q) => $q->where('status', $request->campaign_status));
        }

        $orders = $query->latest('paid_at')->paginate(15)->withQueryString();

        $totalRevenue = (float) AdvertisingOrder::where('status', 'paid')->sum('amount');
        $owners = User::where('role', 'owner')->orderBy('name')->get();
        $packages = AdvertisingPackage::orderBy('name')->get();
        $statuses = collect([
            'draft', 'pending_payment', 'paid', 'pending_review', 'approved',
            'active', 'completed', 'rejected', 'suspended', 'cancelled',
        ]);

        return view('super-admin.advertising.revenue', compact('orders', 'totalRevenue', 'owners', 'packages', 'statuses'));
    }

    public function exportCsv(Request $request)
    {
        $this->authorize('viewAny', AdvertisingCampaign::class);

        $query = AdvertisingOrder::with(['campaign.owner', 'campaign.kos', 'campaign.package'])
            ->where('status', 'paid');

        if ($request->filled('start_date')) {
            $query->whereDate('paid_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('paid_at', '<=', $request->end_date);
        }
        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }
        if ($request->filled('package_id')) {
            $query->whereHas('campaign', fn ($q) => $q->where('package_id', $request->package_id));
        }
        if ($request->filled('campaign_status')) {
            $query->whereHas('campaign', fn ($q) => $q->where('status', $request->campaign_status));
        }

        $items = $query->latest('paid_at')->get();

        $filename = 'laporan-advertising.csv';
        $handle = fopen('php://temp', 'r+');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($handle, ['KosManager - Laporan Revenue Advertising'], ',');
        if ($request->start_date || $request->end_date) {
            fputcsv($handle, ['Periode: '.($request->start_date ?? '-').' s/d '.($request->end_date ?? '-')], ',');
        }
        fputcsv($handle, [], ',');
        fputcsv($handle, ['No', 'Tanggal', 'Campaign', 'Owner', 'Kos', 'Paket', 'Durasi', 'Nominal', 'Status'], ',');
        $no = 1;
        foreach ($items as $item) {
            $c = $item->campaign;
            fputcsv($handle, [
                $no++,
                $item->paid_at?->format('d/m/Y H:i') ?? '-',
                $c->campaign_number ?? '-',
                $c->owner->name ?? '-',
                $c->kos->name ?? '-',
                $c->package->name ?? '-',
                $c->package?->duration_days.' hari' ?? '-',
                'Rp '.number_format($item->amount, 0, ',', '.'),
                AdvertisingLabels::campaignLabel($c->status),
            ], ',');
        }
        fputcsv($handle, [], ',');
        fputcsv($handle, ['Total Revenue', 'Rp '.number_format((float) AdvertisingOrder::where('status', 'paid')->sum('amount'), 0, ',', '.')], ',');
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
