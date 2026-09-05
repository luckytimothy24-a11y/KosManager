<?php

namespace App\Policies;

use App\Models\AdvertisingCampaign;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdvertisingCampaignPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner();
    }

    public function view(User $user, AdvertisingCampaign $campaign): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        return $this->isOwner($user, $campaign);
    }

    /**
     * Membuat campaign:
     * - kos_id null (advertiser PIHAK KETIGA): owner, admin, dan super admin.
     * - kos_id terisi (promosi kos owner): hanya owner untuk kos miliknya.
     */
    public function create(User $user, ?int $kosId = null): bool
    {
        if ($kosId === null) {
            return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner();
        }

        if (! $user->isOwner()) {
            return false;
        }

        return $user->ownedKos()->whereKey($kosId)->exists();
    }

    public function update(User $user, AdvertisingCampaign $campaign): bool
    {
        // Moderator (admin/super admin) dapat mengedit creative/CTA/placement
        // kampanye pihak ketiga selama belum selesai/dibatalkan.
        if (($user->isSuperAdmin() || $user->isAdmin()) && $campaign->kos_id === null) {
            return ! in_array($campaign->status, [
                AdvertisingCampaign::STATUS_COMPLETED,
                AdvertisingCampaign::STATUS_CANCELLED,
            ], true);
        }

        // Owner hanya boleh mengedit campaign yang masih draft/pending_payment.
        return $this->isOwner($user, $campaign)
            && in_array($campaign->status, [
                AdvertisingCampaign::STATUS_DRAFT,
                AdvertisingCampaign::STATUS_PENDING_PAYMENT,
            ]);
    }

    public function pay(User $user, AdvertisingCampaign $campaign): bool
    {
        return $this->isOwner($user, $campaign)
            && $campaign->status === AdvertisingCampaign::STATUS_PENDING_PAYMENT;
    }

    /**
     * Owner dapat membatalkan campaign sebelum aktif.
     */
    public function cancel(User $user, AdvertisingCampaign $campaign): bool
    {
        return $this->isOwner($user, $campaign)
            && in_array($campaign->status, [
                AdvertisingCampaign::STATUS_PENDING_PAYMENT,
                AdvertisingCampaign::STATUS_PAID,
                AdvertisingCampaign::STATUS_PENDING_REVIEW,
            ]);
    }

    public function reviewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function review(User $user, AdvertisingCampaign $campaign): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            // Campaign pihak ketiga (tanpa kos) bersifat global — valid untuk semua admin.
            if ($campaign->kos_id === null) {
                return true;
            }

            return $user->canAccessKos((int) $campaign->kos_id);
        }

        return false;
    }

    public function approve(User $user, AdvertisingCampaign $campaign): bool
    {
        return $this->review($user, $campaign)
            && $campaign->status === AdvertisingCampaign::STATUS_PENDING_REVIEW;
    }

    public function reject(User $user, AdvertisingCampaign $campaign): bool
    {
        return $this->review($user, $campaign)
            && $campaign->status === AdvertisingCampaign::STATUS_PENDING_REVIEW;
    }

    public function suspend(User $user, AdvertisingCampaign $campaign): bool
    {
        return $this->review($user, $campaign)
            && $campaign->status === AdvertisingCampaign::STATUS_ACTIVE;
    }

    private function isOwner(User $user, AdvertisingCampaign $campaign): bool
    {
        if (! $user->isOwner() || (int) $campaign->owner_id !== (int) $user->id) {
            return false;
        }

        // Campaign pihak ketiga: pemilik campaign (advertiser) memang owner-nya.
        if ($campaign->kos_id === null) {
            return true;
        }

        return (int) $campaign->kos->owner_id === (int) $user->id;
    }
}
