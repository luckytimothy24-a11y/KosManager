<?php

namespace Tests\Feature;

use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $admin;

    private User $owner;

    private User $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
    }

    // ========== SUPER ADMIN ACCESS ==========

    public function test_super_admin_can_create_admin(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/super-admin/admins', [
            'name' => 'New Admin',
            'email' => 'newadmin@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '081234567890',
        ]);
        $response->assertRedirect('/super-admin/admins');
        $this->assertDatabaseHas('users', ['email' => 'newadmin@test.com', 'role' => 'admin']);
    }

    public function test_super_admin_can_deactivate_admin(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('super-admin.admins.deactivate', $this->admin));
        $response->assertRedirect(route('super-admin.admins.index'));
        $this->assertFalse($this->admin->fresh()->is_active);
    }

    public function test_super_admin_can_assign_admin_to_kos(): void
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->actingAs($this->superAdmin)->post(route('super-admin.admins.assign', $this->admin), [
            'kos_id' => $kos->id,
        ]);
        $this->assertTrue($this->admin->assignedKos()->where('kos_id', $kos->id)->exists());
    }

    public function test_super_admin_can_unassign_admin_from_kos(): void
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->admin->assignedKos()->attach($kos->id);
        $this->actingAs($this->superAdmin)->post(route('super-admin.admins.unassign', [$this->admin, $kos]));
        $this->assertFalse($this->admin->assignedKos()->where('kos_id', $kos->id)->exists());
    }

    public function test_admin_creation_sets_role_server_side(): void
    {
        $this->actingAs($this->superAdmin)->post('/super-admin/admins', [
            'name' => 'Hacker Admin',
            'email' => 'hacker@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'super_admin',
        ]);
        $this->assertDatabaseHas('users', ['email' => 'hacker@test.com', 'role' => 'admin']);
    }

    public function test_super_admin_can_filter_admins_by_status(): void
    {
        $this->admin->update(['is_active' => false]);
        $response = $this->actingAs($this->superAdmin)->get('/super-admin/admins?status=inactive');
        $response->assertOk();
        $response->assertSee($this->admin->name);
    }

    // ========== DENIED ROLES ==========

    public function test_admin_cannot_access_admin_management(): void
    {
        $response = $this->actingAs($this->admin)->get('/super-admin/admins');
        $response->assertStatus(403);
    }

    public function test_admin_cannot_create_admin(): void
    {
        $response = $this->actingAs($this->admin)->get('/super-admin/admins/create');
        $response->assertStatus(403);
    }

    public function test_owner_cannot_access_admin_management(): void
    {
        $response = $this->actingAs($this->owner)->get('/super-admin/admins');
        $response->assertStatus(403);
    }

    public function test_tenant_cannot_access_admin_management(): void
    {
        $response = $this->actingAs($this->tenant)->get('/super-admin/admins');
        $response->assertStatus(403);
    }

    // ========== ASSIGNMENT ISOLATION ==========

    public function test_cannot_assign_inactive_admin(): void
    {
        $this->admin->update(['is_active' => false]);
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $response = $this->actingAs($this->superAdmin)->post(route('super-admin.admins.assign', $this->admin), [
            'kos_id' => $kos->id,
        ]);
        $response->assertStatus(400);
    }

    public function test_assignment_does_not_change_ownership(): void
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $originalOwnerId = $kos->owner_id;
        $this->actingAs($this->superAdmin)->post(route('super-admin.admins.assign', $this->admin), [
            'kos_id' => $kos->id,
        ]);
        $this->assertDatabaseHas('kos', ['id' => $kos->id, 'owner_id' => $originalOwnerId]);
    }
}
