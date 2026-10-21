<?php

namespace App\Enums;

/**
 * Centralized role representation for KosManager.
 *
 * The `users.role` column stores plain strings (DB enum), so this enum is
 * used for comparisons and validation WITHOUT casting the model attribute.
 * Do NOT add a `$casts` entry for `role`: casting would change the attribute
 * type and break existing string comparisons across the codebase.
 */
enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case OWNER = 'owner';
    case TENANT = 'tenant';

    /**
     * All valid role values, e.g. for `Rule::in(UserRole::values())`.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $role) => $role->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin Kos',
            self::OWNER => 'Owner',
            self::TENANT => 'Tenant',
        };
    }

    /**
     * System-level authority: manages users, roles, assignments,
     * global settings, advertising and partners.
     */
    public function isSystemLevel(): bool
    {
        return $this === self::SUPER_ADMIN;
    }

    /**
     * Operational management authority: works within assigned scope,
     * never system-level configuration.
     */
    public function isOperationalManager(): bool
    {
        return $this === self::ADMIN;
    }

    public static function tryFromRole(?string $role): ?self
    {
        if ($role === null) {
            return null;
        }

        return self::tryFrom($role);
    }
}
