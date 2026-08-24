<?php

namespace App\Policies;

use App\Models\Kos;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KosPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner();
    }

    public function view(User $user, Kos $kos): bool
    {
        return $user->isSuperAdmin()
            || ($user->isAdmin() && $user->canAccessKos((int) $kos->id))
            || (int) $kos->owner_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner();
    }

    public function update(User $user, Kos $kos): bool
    {
        return $user->isSuperAdmin()
            || ($user->isAdmin() && $user->canAccessKos((int) $kos->id))
            || (int) $kos->owner_id === (int) $user->id;
    }

    public function delete(User $user, Kos $kos): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $hasActiveTenant = $kos->penghunis()->where('status', 'active')->count() > 0;

        if ($user->isAdmin()) {
            return $user->canAccessKos((int) $kos->id) && ! $hasActiveTenant;
        }

        if ($user->isOwner() && (int) $kos->owner_id === (int) $user->id) {
            return ! $hasActiveTenant;
        }

        return false;
    }

    public function manage(User $user, Kos $kos): bool
    {
        return $this->update($user, $kos);
    }
}
