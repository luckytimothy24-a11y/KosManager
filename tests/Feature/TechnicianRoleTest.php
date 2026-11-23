<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TechnicianRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_technician_role_exists_in_database_enum(): void
    {
        $this->assertContains('technician', $this->enumValues('users', 'role'));
    }

    public function test_user_role_enum_includes_technician(): void
    {
        $this->assertContains(UserRole::TECHNICIAN, UserRole::cases());
        $this->assertSame('technician', UserRole::TECHNICIAN->value);
        $this->assertSame('Teknisi', UserRole::TECHNICIAN->label());
        $this->assertContains('technician', UserRole::values());
    }

    public function test_is_technician_helper(): void
    {
        $technician = User::factory()->create(['role' => 'technician']);
        $other = User::factory()->create(['role' => 'pencari']);

        $this->assertTrue($technician->isTechnician());
        $this->assertFalse($other->isTechnician());
    }

    public function test_technician_is_not_internal_or_system_level(): void
    {
        $this->assertFalse(UserRole::TECHNICIAN->isSystemLevel());
        $this->assertNotContains('technician', config('roles.internal'));
        $this->assertContains('technician', config('roles.canonical'));
    }

    public function test_roles_config_loads_mapping(): void
    {
        $this->assertSame('pencari', config('roles.legacy_map.tenant'));
        $this->assertSame('pencari', config('roles.aliases.tenant'));
    }

    public function test_tenant_is_still_not_a_database_role(): void
    {
        $this->assertNotContains('tenant', $this->enumValues('users', 'role'));
        $this->assertNull(UserRole::tryFrom('tenant'));
    }

    /** @return array<int, string> */
    private function enumValues(string $table, string $column): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $sql = (string) DB::table('sqlite_master')
                ->where('type', 'table')
                ->where('name', $table)
                ->value('sql');

            if (preg_match('/"'.preg_quote($column, '/').'"\s+in\s*\(([^)]*)\)/i', $sql, $matches)) {
                preg_match_all("/'([^']+)'/", $matches[1], $values);

                return $values[1];
            }

            return [];
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $row = DB::selectOne("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
            $type = (string) ($row->Type ?? '');
            if (preg_match('/^enum\((.*)\)$/i', $type, $matches)) {
                preg_match_all("/'([^']+)'/", $matches[1], $values);

                return $values[1];
            }

            return [];
        }

        $this->fail("Unsupported database driver: {$driver}");
    }
}
