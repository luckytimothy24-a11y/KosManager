<?php

namespace Tests\Feature;

use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerManagementTest extends TestCase
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

    public function test_super_admin_can_access_owner_list(): void
    {
        User::factory()->create(['role' => 'owner', 'name' => 'Test Owner']);

        $response = $this->actingAs($this->superAdmin)->get('/super-admin/owners');

        $response->assertOk();
        $response->assertSee('Test Owner');
    }

    public function test_super_admin_can_view_owner_detail(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('super-admin.owners.show', $this->owner));

        $response->assertOk();
        $response->assertSee($this->owner->name);
    }

    public function test_super_admin_can_deactivate_owner(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('super-admin.owners.deactivate', $this->owner));

        $response->assertRedirect(route('super-admin.owners.index'));
        $this->assertFalse($this->owner->fresh()->is_active);
    }

    public function test_super_admin_can_search_owners(): void
    {
        User::factory()->create(['role' => 'owner', 'name' => 'Alpha Owner', 'email' => 'alpha@test.com']);

        $response = $this->actingAs($this->superAdmin)->get('/super-admin/owners?search=Alpha');

        $response->assertOk();
        $response->assertSee('Alpha Owner');
    }

    public function test_super_admin_can_filter_owners_by_status(): void
    {
        $this->owner->update(['is_active' => false]);

        $response = $this->actingAs($this->superAdmin)->get('/super-admin/owners?status=inactive');

        $response->assertOk();
        $response->assertSee($this->owner->name);
    }

    public function test_super_admin_can_filter_owners_with_kos(): void
    {
        Kos::factory()->create(['owner_id' => $this->owner->id]);

        $response = $this->actingAs($this->superAdmin)->get('/super-admin/owners?has_kos=1');

        $response->assertOk();
        $response->assertSee($this->owner->name);
    }

    // ========== DENIED ROLES ==========

    public function test_admin_cannot_access_owner_management(): void
    {
        $response = $this->actingAs($this->admin)->get('/super-admin/owners');
        $response->assertStatus(403);
    }

    public function test_admin_cannot_view_owner_detail(): void
    {
        $response = $this->actingAs($this->admin)->get(route('super-admin.owners.show', $this->owner));
        $response->assertStatus(403);
    }

    public function test_owner_cannot_access_owner_management(): void
    {
        $response = $this->actingAs($this->owner)->get('/super-admin/owners');
        $response->assertStatus(403);
    }

    public function test_tenant_cannot_access_owner_management(): void
    {
        $response = $this->actingAs($this->tenant)->get('/super-admin/owners');
        $response->assertStatus(403);
    }

    // ========== IDOR ==========

    public function test_owner_cannot_view_another_owners_detail(): void
    {
        $otherOwner = User::factory()->create(['role' => 'owner']);
        $response = $this->actingAs($this->owner)->get(route('super-admin.owners.show', $otherOwner));
        $response->assertStatus(403);
    }

    public function test_deactivation_does_not_hard_delete(): void
    {
        $this->owner->update(['is_active' => true]);
        $this->actingAs($this->superAdmin)->post(route('super-admin.owners.deactivate', $this->owner));
        $this->assertDatabaseHas('users', ['id' => $this->owner->id, 'is_active' => false]);
    }
}
