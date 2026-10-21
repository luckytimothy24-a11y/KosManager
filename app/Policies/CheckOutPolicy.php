<?php

namespace App\Policies;

use App\Models\CheckOut;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CheckOutPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner();
    }

    public function view(User $user, CheckOut $checkOut): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            // Fail-closed bila penghuni terhapus: tolak alih-alih error 500.
            $kosId = $checkOut->penghuni?->kos_id;

            return $kosId !== null && $user->canAccessKos((int) $kosId);
        }

        if ((int) ($checkOut->penghuni?->user_id) === (int) $user->id) {
            return true;
        }

        $ownerId = $checkOut->penghuni?->kos?->owner_id;

        return $ownerId !== null && (int) $ownerId === (int) $user->id;
    }

    public function approve(User $user, CheckOut $checkOut): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            $kosId = $checkOut->penghuni?->kos_id;

            return $kosId !== null && $user->canAccessKos((int) $kosId);
        }

        $ownerId = $checkOut->penghuni?->kos?->owner_id;

        return $ownerId !== null && (int) $ownerId === (int) $user->id;
    }

    public function reject(User $user, CheckOut $checkOut): bool
    {
        return $this->approve($user, $checkOut);
    }

    /**
     * Cancel untuk pengelola dalam scope kos yang sama, plus tenant
     * atas pengajuan miliknya sendiri. Pembatasan status (hanya
     * pending) ditegakkan di controller.
     */
    public function cancel(User $user, CheckOut $checkOut): bool
    {
        return $this->approve($user, $checkOut)
            || (int) $checkOut->penghuni->user_id === (int) $user->id;
    }
}
