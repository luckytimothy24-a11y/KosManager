<?php

namespace App\Policies;

use App\Models\PropertyOwner;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Authorization Pemilik Kos Asli (property owner).
 *
 * MVP: hanya Owner (kos scope) dan Super Admin yang boleh mengelola.
 * Admin & Tenant tidak ada akses.
 * Scope kos dicek di service (setPropertyOwner) — policy di sini gate role.
 */
class PropertyOwnerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->isSuperAdmin();
    }

    public function view(User $user, PropertyOwner $propertyOwner): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isOwner()) {
            return $propertyOwner->kos()
                ->where('owner_id', $user->id)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isOwner() || $user->isSuperAdmin();
    }

    /**
     * Update/link property owner ke kos — scope kos dicek di service.
     */
    public function updateAny(User $user): bool
    {
        return $user->isOwner() || $user->isSuperAdmin();
    }

    public function update(User $user, PropertyOwner $propertyOwner): bool
    {
        return $this->view($user, $propertyOwner);
    }

    public function delete(User $user, PropertyOwner $propertyOwner): bool
    {
        return false; // tidak ada fitur hapus di MVP
    }
}
