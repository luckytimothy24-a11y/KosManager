<?php

namespace App\Policies;

use App\Models\User;
use App\Models\User as UserModel;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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

    public function view(User $user, UserModel $targetUser): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Tier 1 — self access: every role, including admin, may view itself.
        if ($this->isSelf($user, $targetUser)) {
            return true;
        }

        if ($user->isAdmin()) {
            // Tier 3 — admin-to-admin boundary (Admin A != Admin B).
            if ($targetUser->isAdmin()) {
                return false;
            }

            // Tier 2 — scoped operational access (owners/tenants in scope).
            return $this->isAdminScopedUser($user, $targetUser);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, UserModel $targetUser): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Tier 1 — self access. Sensitive fields (role, is_active) are
        // stripped for non-super-admin updaters at the controller layer.
        if ($this->isSelf($user, $targetUser)) {
            return true;
        }

        if ($user->isAdmin()) {
            // Tier 3 — admin-to-admin boundary (Admin A != Admin B).
            if ($targetUser->isAdmin()) {
                return false;
            }

            // Tier 2 — scoped operational access (owners/tenants in scope).
            return $this->isAdminScopedUser($user, $targetUser);
        }

        return false;
    }

    public function delete(User $user, UserModel $targetUser): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $this->isAdminScopedUser($user, $targetUser)
                && ! $targetUser->isSuperAdmin()
                && ! $targetUser->isAdmin()
                && ! $targetUser->isOwner();
        }

        return false;
    }

    public function manageRole(User $user, UserModel $targetUser): bool
    {
        return $this->adminPolicy()->manageRole($user, $targetUser);
    }

    /**
     * Admin → Kos assignment authority: super admin only, delegated to
     * AdminPolicy so the boundary lives in exactly one place.
     */
    public function manageAssignment(User $user, UserModel $targetUser): bool
    {
        return $this->adminPolicy()->manageAssignment($user, $targetUser);
    }

    public function toggleActive(User $user, UserModel $targetUser): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            // Tier 3 — admin and super-admin targets are never toggleable
            // by another admin (delegated boundary, explicit over implicit).
            if ($targetUser->isSuperAdmin() || $targetUser->isAdmin()) {
                return false;
            }

            if ($this->isSelf($user, $targetUser)) {
                return false;
            }

            // Tier 2 — scoped operational users only.
            return $this->isAdminScopedUser($user, $targetUser);
        }

        return false;
    }

    private function isSelf(User $user, UserModel $targetUser): bool
    {
        return (int) $targetUser->id === (int) $user->id;
    }

    private function adminPolicy(): AdminPolicy
    {
        return app(AdminPolicy::class);
    }

    private function isAdminScopedUser(User $admin, UserModel $targetUser): bool
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
