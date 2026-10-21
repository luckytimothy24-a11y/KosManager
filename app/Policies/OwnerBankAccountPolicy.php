<?php

namespace App\Policies;

use App\Models\OwnerBankAccount;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OwnerBankAccountPolicy
{
    use HandlesAuthorization;

    /**
     * Hanya Owner yang login yang boleh mengelola rekening miliknya sendiri.
     * Super admin hanya dapat view (opsional), TIDAK arbitrary edit.
     */
    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->isSuperAdmin();
    }

    public function view(User $user, OwnerBankAccount $account): bool
    {
        return $user->isSuperAdmin()
            || ((int) $account->owner_id === (int) $user->id && $user->isOwner());
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    /**
     * Owner hanya boleh mengelola rekening miliknya sendiri.
     * updateAny dipakai oleh controller (tanpa route model binding akun owner lain).
     */
    public function updateAny(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user, OwnerBankAccount $account): bool
    {
        return $user->isOwner()
            && (int) $account->owner_id === (int) $user->id;
    }

    public function delete(User $user, OwnerBankAccount $account): bool
    {
        return false; // tidak ada fitur hapus; update saja
    }
}
