<?php

namespace App\Policies;

use App\Models\Kamar;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KamarPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner();
    }

    public function view(User $user, Kamar $kamar): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->canAccessKos((int) $kamar->kos_id);
        }

        if ($user->isOwner()) {
            return (int) $kamar->kos->owner_id === (int) $user->id;
        }

        if ($user->isTenant()) {
            return $kamar->status === 'available';
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner();
    }

    public function update(User $user, Kamar $kamar): bool
    {
        return $user->isSuperAdmin()
            || ($user->isAdmin() && $user->canAccessKos((int) $kamar->kos_id))
            || ($user->isOwner() && (int) $kamar->kos->owner_id === (int) $user->id);
    }

    public function delete(User $user, Kamar $kamar): bool
    {
        return $this->update($user, $kamar);
    }
}
