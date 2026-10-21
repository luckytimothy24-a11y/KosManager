<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AuditLogPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function view(User $user, AuditLog $log): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $this->isAdminScopedLog($user, $log);
        }

        return false;
    }

    private function isAdminScopedLog(User $admin, AuditLog $log): bool
    {
        $adminKosIds = $admin->assignedKos()->pluck('kos.id');

        if ($adminKosIds->isEmpty()) {
            return false;
        }

        $logKosId = $log->kos_id ?? null;
        if ($logKosId && $adminKosIds->contains((int) $logKosId)) {
            return true;
        }

        if ($log->module === 'Auth' && $log->action === 'Login' && (int) $log->user_id === (int) $admin->id) {
            return true;
        }

        return false;
    }
}
