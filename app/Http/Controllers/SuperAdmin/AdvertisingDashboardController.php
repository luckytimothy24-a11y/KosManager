<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AdvertisingCampaign;
use App\Services\AdvertisingAnalytics;

class AdvertisingDashboardController extends Controller
{
    public function index(AdvertisingAnalytics $analytics)
    {
        $this->authorize('viewAny', AdvertisingCampaign::class);

        $stats = $analytics->overview();

        $recentCampaigns = AdvertisingCampaign::with(['owner', 'kos', 'package'])
            ->latest()
            ->limit(8)
            ->get();

        return view('super-admin.advertising.dashboard', compact('stats', 'recentCampaigns'));
    }
}
