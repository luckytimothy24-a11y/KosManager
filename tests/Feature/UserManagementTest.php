<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
    }

    public function test_super_admin_can_view_user_list(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('super-admin.users.index'));
        $response->assertStatus(200);
    }

    public function test_owner_cannot_view_user_list(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $response = $this->actingAs($owner)->get(route('super-admin.users.index'));
        $response->assertStatus(403);
    }

    public function test_super_admin_can_create_user(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('super-admin.users.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'tenant',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('super-admin.users.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'tenant',
        ]);
    }

    public function test_super_admin_can_update_user(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);

        $response = $this->actingAs($this->superAdmin)->put(route('super-admin.users.update', $user), [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => 'tenant',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'is_active' => false,
        ]);
    }

    public function test_super_admin_can_delete_user(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);

        $response = $this->actingAs($this->superAdmin)->delete(route('super-admin.users.destroy', $user));
        $response->assertRedirect(route('super-admin.users.index'));
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_super_admin_can_view_create_form(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('super-admin.users.create'));
        $response->assertStatus(200);
    }

    public function test_super_admin_can_view_edit_form(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($this->superAdmin)->get(route('super-admin.users.edit', $user));
        $response->assertStatus(200);
    }
}
