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
        return $user->hasRole('super_admin', 'admin', 'owner', 'tenant');
    }

    public function view(User $user, Tagihan $tagihan): bool
    {
        return $user->isSuperAdmin()
            || ($user->isAdmin() && $user->canAccessKos((int) $tagihan->kamar->kos_id))
            || (int) $tagihan->kamar->kos->owner_id === (int) $user->id
            || (int) $tagihan->penghuni->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin', 'admin', 'owner');
    }

    public function update(User $user): bool
    {
        return $user->hasRole('super_admin', 'admin', 'owner');
    }

    public function verify(User $user): bool
    {
        return $user->hasRole('super_admin', 'admin', 'owner');
    }
}
