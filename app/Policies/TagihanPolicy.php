<?php

namespace App\Policies;

use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TagihanPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner() || $user->isTenant();
    }

    public function view(User $user, Tagihan $tagihan): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            // Fail-closed bila kamar terhapus: tolak alih-alih error 500.
            $kosId = $tagihan->kamar?->kos_id;

            return $kosId !== null && $user->canAccessKos((int) $kosId);
        }

        if ((int) ($tagihan->penghuni?->user_id) === (int) $user->id) {
            return true;
        }

        $ownerId = $tagihan->kamar?->kos?->owner_id;

        return $ownerId !== null && (int) $ownerId === (int) $user->id;
    }
}
