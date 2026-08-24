<?php

namespace App\Policies;

use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PembayaranPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin', 'admin', 'owner', 'tenant');
    }

    public function view(User $user, Pembayaran $pembayaran): bool
    {
        return $user->isSuperAdmin()
            || ($user->isAdmin() && $user->canAccessKos((int) $pembayaran->penghuni->kos_id))
            || (int) $pembayaran->penghuni->kamar->kos->owner_id === (int) $user->id
            || (int) $pembayaran->penghuni->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isTenant();
    }

    public function verify(User $user, Pembayaran $pembayaran): bool
    {
        return $user->isSuperAdmin()
            || ($user->isAdmin() && $user->canAccessKos((int) $pembayaran->penghuni->kos_id))
            || (int) $pembayaran->penghuni->kamar->kos->owner_id === (int) $user->id;
    }
}
