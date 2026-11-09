<?php

namespace App\Traits;

use App\Enums\UserRole;

trait HasRole
{
    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SUPER_ADMIN->value;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN->value;
    }

    public function isOwner(): bool
    {
        return $this->role === UserRole::OWNER->value;
    }

    public function isPencari(): bool
    {
        return $this->role === UserRole::PENCARI->value;
    }

    /**
     * Backward-compatible alias for existing code paths.
     */
    public function isTenant(): bool
    {
        return $this->isPencari();
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles);
    }
}
