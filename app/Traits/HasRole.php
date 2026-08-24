<?php

namespace App\Traits;

trait HasRole
{
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isTenant(): bool
    {
        return $this->role === 'tenant';
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles);
    }
}
