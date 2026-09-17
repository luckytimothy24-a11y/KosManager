<?php

namespace App\Policies;

use App\Models\Kos;
use App\Models\KosPhoto;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KosPhotoPolicy
{
    use HandlesAuthorization;

    public function create(User $user, Kos $kos): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->canAccessKos((int) $kos->id);
        }

        if ($user->isOwner()) {
            return (int) $kos->owner_id === (int) $user->id;
        }

        return false;
    }

    public function delete(User $user, KosPhoto $photo): bool
    {
        $kos = $photo->kos;

        if (! $kos) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->canAccessKos((int) $kos->id);
        }

        if ($user->isOwner()) {
            return (int) $kos->owner_id === (int) $user->id;
        }

        return false;
    }

    public function setCover(User $user, KosPhoto $photo): bool
    {
        return $this->delete($user, $photo);
    }
}
