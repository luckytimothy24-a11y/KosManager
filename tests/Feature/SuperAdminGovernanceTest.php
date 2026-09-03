<?php

namespace Tests\Feature;

use App\Models\Fasilitas;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminGovernanceTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        config(['audit.hmac_secret' => str_repeat('a', 64)]);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
    }

    public function test_last_super_admin_cannot_be_demoted(): void
    {
        $other = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($this->superAdmin)->put(route('super-admin.users.update', $this->superAdmin), [
            'name' => $this->superAdmin->name,
            'email' => $this->superAdmin->email,
            'role' => 'admin',
            'is_active' => '1',
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseHas('users', ['id' => $this->superAdmin->id, 'role' => 'super_admin']);
    }

    public function test_last_super_admin_cannot_be_deactivated(): void
    {
        $response = $this->actingAs($this->superAdmin)->put(route('super-admin.users.update', $this->superAdmin), [
            'name' => $this->superAdmin->name,
            'email' => $this->superAdmin->email,
            'role' => 'super_admin',
            'is_active' => '0',
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseHas('users', ['id' => $this->superAdmin->id, 'is_active' => true]);
    }

    public function test_last_super_admin_cannot_delete_own_account_via_admin_panel(): void
    {
        $response = $this->actingAs($this->superAdmin)->delete(route('super-admin.users.destroy', $this->superAdmin));

        $response->assertStatus(400);
        $this->assertDatabaseHas('users', ['id' => $this->superAdmin->id]);
    }

    public function test_last_super_admin_cannot_delete_own_account_via_profile(): void
    {
        $response = $this->actingAs($this->superAdmin)->delete('/profile', [
            'password' => 'password',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->superAdmin->id]);
    }

    public function test_super_admin_can_demote_another_super_admin_when_two_exist(): void
    {
        $secondSuperAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($this->superAdmin)->put(route('super-admin.users.update', $secondSuperAdmin), [
            'name' => $secondSuperAdmin->name,
            'email' => $secondSuperAdmin->email,
            'role' => 'admin',
            'is_active' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $secondSuperAdmin->id, 'role' => 'admin']);
    }

    public function test_super_admin_cannot_demote_self(): void
    {
        $secondSuperAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($this->superAdmin)->put(route('super-admin.users.update', $this->superAdmin), [
            'name' => $this->superAdmin->name,
            'email' => $this->superAdmin->email,
            'role' => 'admin',
            'is_active' => '1',
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseHas('users', ['id' => $this->superAdmin->id, 'role' => 'super_admin']);
    }

    public function test_super_admin_cannot_deactivate_self(): void
    {
        $response = $this->actingAs($this->superAdmin)->put(route('super-admin.users.update', $this->superAdmin), [
            'name' => $this->superAdmin->name,
            'email' => $this->superAdmin->email,
            'role' => 'super_admin',
            'is_active' => '0',
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseHas('users', ['id' => $this->superAdmin->id, 'is_active' => true]);
    }

    public function test_user_store_creates_audit_log(): void
    {
        $this->actingAs($this->superAdmin)->post(route('super-admin.users.store'), [
            'name' => 'Audit Test Owner',
            'email' => 'audit-owner@test.com',
            'role' => 'owner',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Create',
            'module' => 'User',
            'description' => 'Membuat user Audit Test Owner (owner)',
        ]);
    }

    public function test_user_update_creates_audit_log(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($this->superAdmin)->put(route('super-admin.users.update', $user), [
            'name' => 'Updated Tenant',
            'email' => $user->email,
            'role' => 'tenant',
            'is_active' => '1',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Update',
            'module' => 'User',
            'description' => 'Memperbarui user Updated Tenant',
        ]);
    }

    public function test_user_destroy_creates_audit_log(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($this->superAdmin)->delete(route('super-admin.users.destroy', $user));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Delete',
            'module' => 'User',
        ]);
    }

    public function test_fasilitas_store_creates_audit_log(): void
    {
        $this->actingAs($this->superAdmin)->post(route('super-admin.fasilitas.store'), [
            'name' => 'Audit Gym',
            'icon' => 'ri-heart-pulse-line',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Create',
            'module' => 'Fasilitas',
            'description' => 'Membuat fasilitas Audit Gym',
        ]);
    }

    public function test_fasilitas_update_creates_audit_log(): void
    {
        $fasilitas = Fasilitas::factory()->create();

        $this->actingAs($this->superAdmin)->put(route('super-admin.fasilitas.update', $fasilitas), [
            'name' => 'Updated Fasilitas',
            'icon' => $fasilitas->icon,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Update',
            'module' => 'Fasilitas',
            'description' => 'Memperbarui fasilitas Updated Fasilitas',
        ]);
    }

    public function test_fasilitas_destroy_creates_audit_log(): void
    {
        $fasilitas = Fasilitas::factory()->create();

        $this->actingAs($this->superAdmin)->delete(route('super-admin.fasilitas.destroy', $fasilitas));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Delete',
            'module' => 'Fasilitas',
        ]);
    }

    public function test_admin_mutation_audit_logs_are_in_chain(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($this->superAdmin)->post(route('super-admin.users.store'), [
            'name' => 'Chain Test User',
            'email' => 'chain@test.com',
            'role' => 'tenant',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->actingAs($this->superAdmin)->put(route('super-admin.users.update', $user), [
            'name' => 'Updated',
            'email' => $user->email,
            'role' => 'tenant',
            'is_active' => '1',
        ]);

        $this->actingAs($this->superAdmin)->delete(route('super-admin.users.destroy', $user));

        $result = AuditLogService::verifyChain();

        $this->assertTrue($result['valid']);
        $this->assertGreaterThanOrEqual(3, $result['verified']);
    }
}
