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

    /**
     * Form pembuatan kampanye ADVERTISER PIHAK KETIGA oleh super admin
     * (kos_id NULL — tidak terkait kos tertentu, tanpa alur pembayaran owner).
     */
    public function create()
    {
        $this->authorize('create', [AdvertisingCampaign::class, null]);

        $packages = AdvertisingPackage::active()->orderBy('sort_order')->orderBy('price')->get();
        $placements = self::placementOptions();

        return view('super-admin.advertising.campaign.create', compact('packages', 'placements'));
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

        return redirect()->route('super-admin.advertising.campaigns.show', $campaign)
            ->with('success', 'Kampanye iklan pihak ketiga berhasil dibuat.');
    }

    /**
     * Form edit kampanye advertiser pihak ketiga (creative/CTA/placement saja).
     */
    public function edit(AdvertisingCampaign $campaign)
    {
        $this->authorize('update', $campaign);

        $campaign->load('package');
        $packages = AdvertisingPackage::active()->orderBy('sort_order')->orderBy('price')->get();
        $placements = self::placementOptions();

        return view('super-admin.advertising.campaign.edit', compact('campaign', 'packages', 'placements'));
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

        return redirect()->route('super-admin.advertising.campaigns.show', $campaign)
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

        return redirect()->route(auth()->user()->isAdmin() ? 'admin.advertising.index' : 'super-admin.advertising.campaigns.index')
            ->with('success', 'Kampanye berhasil ditolak.');
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

        $status = $request->filled('status') && in_array($request->status, [
            AdvertisingOrder::STATUS_PENDING,
            AdvertisingOrder::STATUS_PAID,
            AdvertisingOrder::STATUS_REFUNDED,
        ], true) ? $request->status : AdvertisingOrder::STATUS_PAID;

        $query = AdvertisingOrder::with(['campaign.owner', 'campaign.kos', 'campaign.package'])
            ->where('status', $status);

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

        $totalRevenue = (float) AdvertisingOrder::where('status', AdvertisingOrder::STATUS_PAID)->sum('amount');
        $pendingRevenue = (float) AdvertisingOrder::where('status', AdvertisingOrder::STATUS_PENDING)->sum('amount');
        $refundedRevenue = (float) AdvertisingOrder::where('status', AdvertisingOrder::STATUS_REFUNDED)->sum('amount');
        $owners = User::where('role', 'owner')->orderBy('name')->get();
        $packages = AdvertisingPackage::orderBy('name')->get();
        $statuses = collect([
            'draft', 'pending_payment', 'paid', 'pending_review', 'approved',
            'active', 'completed', 'rejected', 'suspended', 'cancelled',
        ]);

        return view('super-admin.advertising.revenue', compact('orders', 'totalRevenue', 'pendingRevenue', 'refundedRevenue', 'owners', 'packages', 'statuses', 'status'));
    }

    public function exportCsv(Request $request)
    {
        $this->authorize('viewAny', AdvertisingCampaign::class);

        $status = $request->filled('status') && in_array($request->status, [
            AdvertisingOrder::STATUS_PENDING,
            AdvertisingOrder::STATUS_PAID,
            AdvertisingOrder::STATUS_REFUNDED,
        ], true) ? $request->status : AdvertisingOrder::STATUS_PAID;

        $query = AdvertisingOrder::with(['campaign.owner', 'campaign.kos', 'campaign.package'])
            ->where('status', $status);

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
        fputcsv($handle, ['Status Order: '.AdvertisingLabels::orderLabel($status).' (paid/refunded menandakan status ledger)'], ',');
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
                AdvertisingLabels::orderLabel($item->status),
            ], ',');
        }
        fputcsv($handle, [], ',');
        fputcsv($handle, ['Total Revenue (paid)', 'Rp '.number_format((float) AdvertisingOrder::where('status', 'paid')->sum('amount'), 0, ',', '.')], ',');
        fputcsv($handle, ['Belum Diterima (pending)', 'Rp '.number_format((float) AdvertisingOrder::where('status', 'pending')->sum('amount'), 0, ',', '.')], ',');
        fputcsv($handle, ['Dikembalikan (refunded)', 'Rp '.number_format((float) AdvertisingOrder::where('status', 'refunded')->sum('amount'), 0, ',', '.')], ',');
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
