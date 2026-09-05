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
        return $user->hasRole('super_admin', 'admin', 'owner', 'tenant');
    }

    public function view(User $user, Kontrak $kontrak): bool
    {
        return $user->isSuperAdmin()
            || ($user->isAdmin() && $user->canAccessKos((int) $kontrak->kos_id))
            || (int) $kontrak->kos->owner_id === (int) $user->id
            || (int) $kontrak->penghuni->user_id === (int) $user->id;
    }
}
