<?php

namespace App\Policies;

use App\Models\Fasilitas;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FasilitasPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner();
    }

    public function view(User $user, Fasilitas $fasilitas): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            $assignedKosIds = $user->assignedKos()->pluck('kos.id');

            return (bool) $fasilitas->kos()->whereIn('kos.id', $assignedKosIds)->count()
                || (bool) $fasilitas->kamar()->whereHas('kos', fn ($q) => $q->whereIn('id', $assignedKosIds))->count();
        }

        if ($user->isOwner()) {
            return (bool) $fasilitas->kos()->where('owner_id', $user->id)->count()
                || (bool) $fasilitas->kamar()->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id))->count();
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->assignedKos()->exists();
        }

        return false;
    }

    public function update(User $user, Fasilitas $fasilitas): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            $assignedKosIds = $user->assignedKos()->pluck('kos.id');

            return (bool) $fasilitas->kos()->whereIn('kos.id', $assignedKosIds)->count()
                || (bool) $fasilitas->kamar()->whereHas('kos', fn ($q) => $q->whereIn('id', $assignedKosIds))->count();
        }

        return false;
    }

    public function delete(User $user, Fasilitas $fasilitas): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            $assignedKosIds = $user->assignedKos()->pluck('kos.id');

            return (bool) $fasilitas->kos()->whereIn('kos.id', $assignedKosIds)->count()
                || (bool) $fasilitas->kamar()->whereHas('kos', fn ($q) => $q->whereIn('id', $assignedKosIds))->count();
        }

        return false;
    }
}