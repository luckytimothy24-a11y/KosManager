<?php

namespace App\Policies;

use App\Models\Payout;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Authorization payout ke Pemilik Kos Asli.
 *
 * Matrix:
 * - Owner: view/create/transfer payout untuk KOS MILIKNYA saja (scope via payout.kos.owner_id)
 * - Super Admin: semua + verify/reject
 * - Admin: TIDAK ada akses (MVP)
 * - Tenant: TIDAK ada akses
 *
 * Owner TIDAK boleh self-verify (separation of duties — owner yang transfer).
 */
class PayoutPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->isSuperAdmin();
    }

    public function view(User $user, Payout $payout): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isOwner()) {
            $kos = $payout->kos;

            return $kos && (int) $kos->owner_id === (int) $user->id;
        }

        return false; // admin & tenant tidak boleh lihat payout
    }

    public function create(User $user): bool
    {
        return $user->isOwner() || $user->isSuperAdmin();
    }

    /**
     * Transfer manual + upload bukti — scope sama dengan view
     * (owner kos / super_admin). Payout status dicek di service.
     */
    public function update(User $user, Payout $payout): bool
    {
        return $this->view($user, $payout);
    }

    /**
     * Verifikasi → verified. SUPER ADMIN ONLY — owner tidak boleh self-verify.
     */
    public function verify(User $user, Payout $payout): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Reject → rejected. SUPER ADMIN ONLY.
     */
    public function reject(User $user, Payout $payout): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Download bukti transfer — scope sama dengan view.
     */
    public function downloadProof(User $user, Payout $payout): bool
    {
        return $this->view($user, $payout);
    }
}
