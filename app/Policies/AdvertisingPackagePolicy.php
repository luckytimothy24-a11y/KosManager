<?php

namespace App\Policies;

use App\Models\AdvertisingPackage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdvertisingPackagePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner();
    }

    public function view(User $user, AdvertisingPackage $package): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, AdvertisingPackage $package): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, AdvertisingPackage $package): bool
    {
        return $user->isSuperAdmin();
    }
}
