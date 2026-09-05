<?php

namespace App\Policies;

use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PenghuniPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin', 'admin', 'owner');
    }

    public function view(User $user, Penghuni $penghuni): bool
    {
        return $user->isSuperAdmin()
            || ($user->isAdmin() && $user->canAccessKos((int) $penghuni->kos_id))
            || (int) $penghuni->kos->owner_id === (int) $user->id
            || (int) $penghuni->user_id === (int) $user->id;
    }
}
