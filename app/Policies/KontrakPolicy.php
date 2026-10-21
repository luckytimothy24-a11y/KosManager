<?php

namespace App\Policies;

use App\Models\Kontrak;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KontrakPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner() || $user->isTenant();
    }

    public function view(User $user, Kontrak $kontrak): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin() && $user->canAccessKos((int) $kontrak->kos_id)) {
            return true;
        }

        if ((int) ($kontrak->penghuni?->user_id) === (int) $user->id) {
            return true;
        }

        // Fail-closed bila relasi kos terhapus: tolak alih-alih error 500.
        $ownerId = $kontrak->kos?->owner_id;

        return $ownerId !== null && (int) $ownerId === (int) $user->id;
    }
}
