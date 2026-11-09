<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_access_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_owner_can_access_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_pencari_can_access_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'pencari']);
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_pencari_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create(['role' => 'pencari']);
        $response = $this->actingAs($user)->get('/admin/kos');
        $this->assertTrue(in_array($response->getStatusCode(), [403, 302]));
    }

    public function test_owner_cannot_access_superadmin_routes(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        $response = $this->actingAs($user)->get('/super-admin/users');
        $this->assertTrue(in_array($response->getStatusCode(), [403, 302]));
    }

    public function test_admin_cannot_access_superadmin_routes(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)->get('/super-admin/users');
        $this->assertTrue(in_array($response->getStatusCode(), [403, 302]));
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect(route('login'));
    }
}
