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

    public function isTenant(): bool
    {
        return $this->role === UserRole::TENANT->value;
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles);
    }
}
