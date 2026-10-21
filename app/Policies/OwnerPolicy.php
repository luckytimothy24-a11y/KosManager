<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OwnerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function view(User $user, User $owner): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $this->isAdminScopedOwner($user, $owner);
        }

        return false;
    }

    public function update(User $user, User $owner): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $this->isAdminScopedOwner($user, $owner);
        }

        return false;
    }

    public function toggleActive(User $user, User $owner): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $this->isAdminScopedOwner($user, $owner)
                && ! $owner->isSuperAdmin();
        }

        return false;
    }

    private function isAdminScopedOwner(User $admin, User $owner): bool
    {
        if ($owner->isSuperAdmin() || $owner->isAdmin()) {
            return false;
        }

        if ((int) $owner->id === (int) $admin->id) {
            return false;
        }

        return (bool) $owner->ownedKos()
            ->whereHas('admins', function ($q) use ($admin) {
                $q->where('users.id', $admin->id);
            })
            ->count();
    }
}
