<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Authorization boundary for admin-related management.
 *
 * Registered as the default policy for User::class in AuthServiceProvider.
 * Separates four distinct access tiers:
 *
 *   1. self access          — every user may view/update their own profile
 *  2. operational access    — admin manages owners/tenants within assigned Kos
 *  3. admin management      — creating / updating / activating admins and
 *                             managing admin → kos assignment: SUPER ADMIN ONLY
 *  4. super-admin management — managing super admins: SUPER ADMIN ONLY
 *
 * Admin A never gains access to Admin B through this policy.
 */
class AdminPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Admin detail access: self or super admin.
     * Admin can also view scoped users (owners/tenants within assigned Kos).
     * Admin-to-admin boundary: Admin A != Admin B.
     */
    public function view(User $user, User $target): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($this->isSelf($user, $target)) {
            return true;
        }

        if ($user->isAdmin()) {
            if ($target->isAdmin()) {
                return false;
            }

            return $this->isAdminScopedUser($user, $target);
        }

        return false;
    }

    /**
     * Admin profile update: self (profile fields only — enforced by
     * the request/controller layer) or super admin. Never another admin.
     */
    public function update(User $user, User $target): bool
    {
        if ($this->isSelf($user, $target)) {
            return true;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            if ($target->isAdmin()) {
                return false;
            }

            return $this->isAdminScopedUser($user, $target);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, User $target): bool
    {
        if ($this->isSelf($user, $target)) {
            return true;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $this->isAdminScopedUser($user, $target)
                && ! $target->isSuperAdmin()
                && ! $target->isAdmin()
                && ! $target->isOwner();
        }

        return false;
    }

    /**
     * Admin → Kos assignment authority belongs to Super Admin exclusively.
     * Admins work BASED on assignment; they never grant, revoke or take over
     * assignments — not their own, not another admin's.
     */
    public function manageAssignment(User $actor, User $target): bool
    {
        return $actor->isSuperAdmin();
    }

    public function toggleActive(User $actor, User $target): bool
    {
        if ($actor->isSuperAdmin()) {
            return true;
        }

        if ($actor->isAdmin()) {
            if ($target->isSuperAdmin() || $target->isAdmin()) {
                return false;
            }

            if ($this->isSelf($actor, $target)) {
                return false;
            }

            return $this->isAdminScopedUser($actor, $target);
        }

        return false;
    }

    public function manageRole(User $actor, User $target): bool
    {
        return $actor->isSuperAdmin();
    }

    public function isSelf(User $actor, User $target): bool
    {
        return (int) $actor->id === (int) $target->id;
    }

    /**
     * Check if target user is within the admin's operational scope.
     * Admins can only access owners/tenants whose kos are assigned to them.
     */
    private function isAdminScopedUser(User $admin, User $targetUser): bool
    {
        if ($targetUser->isSuperAdmin() || $targetUser->isAdmin()) {
            return false;
        }

        if ((int) $targetUser->id === (int) $admin->id) {
            return false;
        }

        if ($targetUser->isOwner()) {
            return (bool) $targetUser->ownedKos()
                ->whereHas('admins', function ($q) use ($admin) {
                    $q->where('users.id', $admin->id);
                })
                ->count();
        }

        if ($targetUser->isTenant()) {
            return (bool) $targetUser->penghunis()
                ->whereHas('kos.admins', function ($q) use ($admin) {
                    $q->where('users.id', $admin->id);
                })
                ->count();
        }

        return false;
    }
}
