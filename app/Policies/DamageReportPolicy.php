<?php

namespace App\Policies;

use App\Models\DamageReport;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DamageReportPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner();
    }

    public function view(User $user, DamageReport $damageReport): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Tenant hanya boleh melihat laporan miliknya sendiri.
        if ((int) $damageReport->user_id === (int) $user->id) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->canAccessKos((int) $damageReport->kos_id);
        }

        $ownerId = $damageReport->kos?->owner_id;

        return $ownerId !== null && (int) $ownerId === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isTenant();
    }

    /**
     * Owner, admin operasional, dan super admin boleh membuat laporan
     * atas nama tenant/penghuni. Scope kos diverifikasi server-side di
     * controller — policy ini hanya gerbang peran.
     */
    public function createForOwner(User $user): bool
    {
        return $user->isOwner() || $user->isAdmin() || $user->isSuperAdmin();
    }

    public function moderate(User $user, DamageReport $damageReport): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->canAccessKos((int) $damageReport->kos_id);
        }

        return false;
    }

    /**
     * Tenant boleh menarik laporannya sendiri selama masih pending.
     * Record dipertahankan (soft-delete) untuk jejak audit.
     */
    public function delete(User $user, DamageReport $damageReport): bool
    {
        return $user->isTenant()
            && (int) $damageReport->user_id === (int) $user->id
            && $damageReport->status === DamageReport::STATUS_PENDING;
    }

    public function update(User $user, DamageReport $damageReport): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->canAccessKos((int) $damageReport->kos_id);
        }

        $ownerId = $damageReport->kos?->owner_id;

        return $ownerId !== null && (int) $ownerId === (int) $user->id;
    }
}
