<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    public function test_values_match_database_enum(): void
    {
        $this->assertSame(['super_admin', 'admin', 'owner', 'tenant'], UserRole::values());
    }

    public function test_try_from_role(): void
    {
        $this->assertSame(UserRole::ADMIN, UserRole::tryFromRole('admin'));
        $this->assertSame(UserRole::SUPER_ADMIN, UserRole::tryFromRole('super_admin'));
        $this->assertNull(UserRole::tryFromRole('root'));
        $this->assertNull(UserRole::tryFromRole(null));
    }

    public function test_only_super_admin_is_system_level(): void
    {
        $this->assertTrue(UserRole::SUPER_ADMIN->isSystemLevel());
        $this->assertFalse(UserRole::ADMIN->isSystemLevel());
        $this->assertFalse(UserRole::OWNER->isSystemLevel());
        $this->assertFalse(UserRole::TENANT->isSystemLevel());
    }

    public function test_only_admin_is_operational_manager(): void
    {
        $this->assertTrue(UserRole::ADMIN->isOperationalManager());
        $this->assertFalse(UserRole::SUPER_ADMIN->isOperationalManager());
        $this->assertFalse(UserRole::OWNER->isOperationalManager());
        $this->assertFalse(UserRole::TENANT->isOperationalManager());
    }

    public function test_labels_are_defined(): void
    {
        foreach (UserRole::cases() as $role) {
            $this->assertNotEmpty($role->label());
        }
    }
}
