<?php

namespace App\Policies;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingOrder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdvertisingOrderPolicy
{
    use HandlesAuthorization;

    /**
     * Moderator (admin/super admin) dapat mengkonfirmasi penerimaan dana untuk
     * order pihak ketiga (campaign kos_id NULL) yang masih berstatus pending.
     * Order milik owner TIDAK dapat di-mark paid oleh moderator — owner flow
     * sudah menyelesaikan pembayarannya sendiri.
     *
     * M3 gating: hanya boleh dikonfirmasi bila kampanye masih menunggu
     * pembayaran (pending_payment) — setelah paid, konfirmasi tidak berlaku.
     */
    public function confirmPayment(User $user, AdvertisingOrder $order): bool
    {
        if (! $user->isSuperAdmin() && ! $user->isAdmin()) {
            return false;
        }

        $campaign = $order->campaign;

        return $campaign
            && $campaign->kos_id === null
            && $campaign->status === AdvertisingCampaign::STATUS_PENDING_PAYMENT
            && $order->isPending();
    }
}
