<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkOrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner() || $user->isTechnician();
    }

    public function view(User $user, WorkOrder $workOrder): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->assignedKos()->where('kos.id', $workOrder->kos_id)->exists();
        }

        if ($user->isOwner()) {
            return $workOrder->kos()->where('owner_id', $user->id)->exists();
        }

        if ($user->isTechnician()) {
            return $workOrder->assigned_technician_id === $user->id;
        }

        // Reporter (tenant) hanya lihat miliknya sendiri.
        return $workOrder->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        // Tenant (pencari) dan admin/owner/super_admin boleh membuat.
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner() || $user->isPencari();
    }

    public function update(User $user, WorkOrder $workOrder): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->assignedKos()->where('kos.id', $workOrder->kos_id)->exists();
        }

        if ($user->isOwner()) {
            return $workOrder->kos()->where('owner_id', $user->id)->exists();
        }

        if ($user->isTechnician()) {
            return $workOrder->assigned_technician_id === $user->id;
        }

        return false;
    }

    public function assign(User $user, WorkOrder $workOrder): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner();
    }

    public function delete(User $user, WorkOrder $workOrder): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->assignedKos()->where('kos.id', $workOrder->kos_id)->exists();
        }

        if ($user->isOwner()) {
            return $workOrder->kos()->where('owner_id', $user->id)->exists();
        }

        // Reporter hanya boleh hapus work order miliknya yang masih pending.
        return $workOrder->user_id === $user->id && $workOrder->status === WorkOrder::STATUS_PENDING;
    }
}
