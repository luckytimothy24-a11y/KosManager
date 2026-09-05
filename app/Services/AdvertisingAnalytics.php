<?php

namespace App\Services;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingEvent;
use App\Models\AdvertisingOrder;

class AdvertisingAnalytics
{
    /**
     * Metrik keseluruhan advertising (untuk dashboard super admin / admin).
     *
     * @return array<string, mixed>
     */
    public function overview(): array
    {
        $now = now();

        $revenue = (float) AdvertisingOrder::where('status', AdvertisingOrder::STATUS_PAID)->sum('amount');
        $revenueMonth = (float) AdvertisingOrder::where('status', AdvertisingOrder::STATUS_PAID)
            ->where('paid_at', '>=', $now->copy()->startOfMonth())->sum('amount');
        $revenueToday = (float) AdvertisingOrder::where('status', AdvertisingOrder::STATUS_PAID)
            ->where('paid_at', '>=', $now->copy()->startOfDay())->sum('amount');

        $active = AdvertisingCampaign::where('status', AdvertisingCampaign::STATUS_ACTIVE)
            ->where('starts_at', '<=', $now)->where('ends_at', '>', $now)->count();
        $pendingReview = (int) AdvertisingCampaign::where('status', AdvertisingCampaign::STATUS_PENDING_REVIEW)->count();
        $pendingPayment = (int) AdvertisingCampaign::where('status', AdvertisingCampaign::STATUS_PENDING_PAYMENT)->count();
        $completed = (int) AdvertisingCampaign::where('status', AdvertisingCampaign::STATUS_COMPLETED)->count();

        $impressions = (int) AdvertisingEvent::where('type', 'impression')->count();
        $clicks = (int) AdvertisingEvent::where('type', 'click')->count();
        $conversions = (int) AdvertisingEvent::where('type', 'conversion')->count();
        $orders = (int) AdvertisingOrder::where('status', AdvertisingOrder::STATUS_PAID)->count();

        $ctr = $impressions > 0 ? round($clicks / $impressions * 100, 2) : 0.0;
        $conversionRate = $clicks > 0 ? round($conversions / $clicks * 100, 2) : 0.0;

        return compact(
            'revenue',
            'revenueMonth',
            'revenueToday',
            'active',
            'pendingReview',
            'pendingPayment',
            'completed',
            'impressions',
            'clicks',
            'conversions',
            'orders',
            'ctr',
            'conversionRate',
        );
    }

    /**
     * Ringkasan performa untuk seorang owner (campaign miliknya).
     *
     * @return array<string, mixed>
     */
    public function ownerOverview(int $ownerId): array
    {
        $now = now();

        $campaigns = AdvertisingCampaign::where('owner_id', $ownerId);

        $spending = (float) AdvertisingOrder::where('owner_id', $ownerId)
            ->where('status', AdvertisingOrder::STATUS_PAID)->sum('amount');

        $active = (clone $campaigns)->where('status', AdvertisingCampaign::STATUS_ACTIVE)
            ->where('starts_at', '<=', $now)->where('ends_at', '>', $now)->count();
        $pending = (int) (clone $campaigns)->where('status', AdvertisingCampaign::STATUS_PENDING_REVIEW)->count();

        $totalImpressions = 0;
        $totalClicks = 0;
        $totalConversions = 0;

        $ownerCampaignIds = (clone $campaigns)->pluck('id');
        if ($ownerCampaignIds->isNotEmpty()) {
            $totalImpressions = (int) AdvertisingEvent::whereIn('campaign_id', $ownerCampaignIds)->where('type', 'impression')->count();
            $totalClicks = (int) AdvertisingEvent::whereIn('campaign_id', $ownerCampaignIds)->where('type', 'click')->count();
            $totalConversions = (int) AdvertisingEvent::whereIn('campaign_id', $ownerCampaignIds)->where('type', 'conversion')->count();
        }

        $ctr = $totalImpressions > 0 ? round($totalClicks / $totalImpressions * 100, 2) : 0.0;
        $conversionRate = $totalClicks > 0 ? round($totalConversions / $totalClicks * 100, 2) : 0.0;

        return compact(
            'spending',
            'active',
            'pending',
            'totalImpressions',
            'totalClicks',
            'totalConversions',
            'ctr',
            'conversionRate',
        );
    }
}
