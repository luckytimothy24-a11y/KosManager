<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy untuk Laporan Kos (owner / admin assigned-kos / super_admin global).
 *
 * Admin = operational staff → scope assigned kos saja.
 * Bukan capability global, bukan capability Owner penuh.
 */
class LaporanPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner();
    }

    /**
     * Tenant tidak boleh mengakses laporan kos/owner/admin.
     */
    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function export(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Akses ke kos tertentu untuk filter/export laporan.
     * Super admin: global. Admin: assigned. Owner: owned.
     */
    public function accessKos(User $user, int $kosId): bool
    {
        return $user->canAccessKos($kosId);
    }
}
