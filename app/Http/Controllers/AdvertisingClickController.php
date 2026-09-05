<?php

namespace App\Http\Controllers;

use App\Models\AdvertisingCampaign;
use App\Models\Kos;
use App\Services\AdvertisingService;
use Illuminate\Http\Request;

class AdvertisingClickController extends Controller
{
    /**
     * Catat klik iklan lalu redirect.
     *
     * Untuk campaign PIHAK KETIGA (kos_id NULL): klik tercatat lalu user
     * dibawa ke destination_url advertiser (jika valid) — konversi eksternal,
     * TIDAK ada atribusi booking kos.
     *
     * Untuk campaign promosi kos owner (legacy): klik tercatat, atribusi
     * conversion disimpan di session, lalu user dibawa ke detail kos.
     *
     * Klik hanya tercatat bila campaign sedang aktif (dalam jendela waktu).
     */
    public function track(Request $request, int $campaignId, AdvertisingService $service)
    {
        $campaign = AdvertisingCampaign::find($campaignId);

        $fallback = redirect()->route('tenant.kos.index');

        if (! $campaign) {
            return $fallback;
        }

        $user = $request->user();

        // Hanya catat klik untuk campaign yang benar-benar aktif saat ini.
        $isLive = $campaign->status === AdvertisingCampaign::STATUS_ACTIVE
            && $campaign->starts_at
            && $campaign->ends_at
            && now()->between($campaign->starts_at, $campaign->ends_at);

        if ($isLive) {
            $service->trackEvent(
                $campaign->id,
                'click',
                $user?->id,
                session()->getId(),
                $request->get('placement', $campaign->placement ?: 'marketplace')
            );

            if ($campaign->isThirdParty()) {
                $destination = $service->destinationFor($campaign);

                return $destination
                    ? redirect()->away($destination)
                    : $fallback;
            }

            // Atribusi klik kos agar conversion dapat diatribusikan bila tenant
            // melakukan booking pada kos ini.
            session(['ad_click' => [
                'kos_id' => (int) $campaign->kos_id,
                'campaign_id' => $campaign->id,
                'at' => now()->timestamp,
            ]]);
        }

        if ($campaign->isThirdParty()) {
            $destination = $service->destinationFor($campaign);

            return $destination
                ? redirect()->away($destination)
                : $fallback;
        }

        $kos = Kos::whereKey($campaign->kos_id)->where('status', 'active')->first();

        if (! $kos) {
            return $fallback;
        }

        return redirect()->route('tenant.kos.show', $kos);
    }
}
