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

        if (! $campaign) {
            return $this->respond($request, redirect()->route('welcome'));
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
                $request->get('placement', $campaign->placement ?: 'marketplace'),
                true
            );

            if ($campaign->isThirdParty()) {
                $destination = $service->destinationFor($campaign);

                return $this->respond($request, $destination
                    ? redirect()->away($destination)
                    : redirect()->route('welcome'));
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

            return $this->respond($request, $destination
                ? redirect()->away($destination)
                : redirect()->route('welcome'));
        }

        $kos = Kos::whereKey($campaign->kos_id)->where('status', 'active')->first();

        if (! $kos) {
            return $this->respond($request, redirect()->route('welcome'));
        }

        if (! $user) {
            return $this->respond($request, redirect()->route('login'));
        }

        return $this->respond($request, redirect()->route('tenant.kos.show', $kos));
    }

    /**
     * For AJAX requests (impression tracking via fetch), return JSON instead of
     * following a redirect which would trigger CORS errors on external URLs.
     */
    private function respond(Request $request, $redirectResponse)
    {
        if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['status' => 'ok']);
        }

        return $redirectResponse;
    }
}
