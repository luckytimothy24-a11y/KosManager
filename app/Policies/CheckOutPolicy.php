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
        return $user->hasRole('super_admin', 'admin', 'owner');
    }

    public function view(User $user, CheckOut $checkOut): bool
    {
        return $user->isSuperAdmin()
            || ($user->isAdmin() && $user->canAccessKos((int) $checkOut->kamar->kos_id))
            || (int) $checkOut->kamar->kos->owner_id === (int) $user->id
            || (int) $checkOut->penghuni->user_id === (int) $user->id;
    }

    public function approve(User $user, CheckOut $checkOut): bool
    {
        return $user->isSuperAdmin()
            || ($user->isAdmin() && $user->canAccessKos((int) $checkOut->kamar->kos_id))
            || (int) $checkOut->kamar->kos->owner_id === (int) $user->id;
    }

    public function reject(User $user, CheckOut $checkOut): bool
    {
        return $this->approve($user, $checkOut);
    }
}
