<?php

namespace App\Policies;

use App\Models\CheckIn;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CheckInPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin', 'admin', 'owner');
    }

    public function view(User $user, CheckIn $checkIn): bool
    {
        return $user->isSuperAdmin()
            || ($user->isAdmin() && $user->canAccessKos((int) $checkIn->kamar->kos_id))
            || (int) $checkIn->kamar->kos->owner_id === (int) $user->id
            || (int) $checkIn->penghuni->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin', 'admin', 'owner');
    }
}
