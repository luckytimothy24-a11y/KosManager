<?php

namespace App\Policies;

use App\Models\PaymentDistribution;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentDistributionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Owner hanya melihat distribution miliknya.
     * Admin: assigned kos dari booking → kos.
     * Super admin: semua (view saja).
     */
    public function view(User $user, PaymentDistribution $distribution): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isOwner()) {
            return (int) $distribution->owner_id === (int) $user->id;
        }

        if ($user->isAdmin()) {
            $booking = $distribution->booking;

            return $booking && $user->canAccessKos((int) $booking->kos_id);
        }

        return false; // tenant tidak boleh lihat distribution owner
    }

    /**
     * Owner menandai distribution sudah dicairkan (manual).
     * Super admin juga boleh (audit trail di service).
     */
    public function markProcessed(User $user, PaymentDistribution $distribution): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isOwner()
            && (int) $distribution->owner_id === (int) $user->id;
    }

    /**
     * Tenant TIDAK boleh memproses payout.
     */
    public function process(User $user, PaymentDistribution $distribution): bool
    {
        return $this->markProcessed($user, $distribution);
    }
}
