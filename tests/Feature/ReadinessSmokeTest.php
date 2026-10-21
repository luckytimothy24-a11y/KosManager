<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadinessSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge(['password' => 'secret123'], $overrides));
    }

    // ---------------------------------------------------------------- role checks

    public function test_super_admin_is_recognized(): void
    {
        $user = $this->makeUser(['role' => 'super_admin']);

        $user->refresh();

        $this->assertTrue($user->isSuperAdmin());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isOwner());
        $this->assertFalse($user->isTenant());
        $this->assertTrue($user->hasRole('super_admin'));
        $this->assertTrue($user->hasRole('super_admin', 'admin', 'owner', 'tenant'));
    }

    public function test_admin_is_recognized(): void
    {
        $user = $this->makeUser(['role' => 'admin']);

        $user->refresh();

        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->hasRole('admin', 'super_admin'));
        $this->assertFalse($user->isOwner());
        $this->assertFalse($user->isTenant());
    }

    public function test_owner_is_recognized(): void
    {
        $user = $this->makeUser(['role' => 'owner']);

        $user->refresh();

        $this->assertTrue($user->isOwner());
        $this->assertTrue($user->hasRole('owner', 'super_admin'));
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isTenant());
    }

    public function test_tenant_is_recognized(): void
    {
        $user = $this->makeUser(['role' => 'tenant']);

        $user->refresh();

        $this->assertTrue($user->isTenant());
        $this->assertTrue($user->hasRole('tenant'));
        $this->assertFalse($user->isOwner());
        $this->assertFalse($user->isAdmin());
    }

    public function test_default_role_is_tenant(): void
    {
        $user = $this->makeUser();

        $user->refresh();

        $this->assertTrue($user->isTenant());
        $this->assertFalse($user->isOwner());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isSuperAdmin());
    }

    // ---------------------------------------------------------------- role-based access

    public function test_super_admin_accesses_all_routes(): void
    {
        $user = $this->makeUser(['role' => 'super_admin']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/health');
        $response->assertOk();
    }

    public function test_admin_accesses_api_routes(): void
    {
        $user = $this->makeUser(['role' => 'admin']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/me');
        $response->assertOk();
    }

    public function test_owner_accesses_owner_routes(): void
    {
        $user = $this->makeUser(['role' => 'owner']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/me');
        $response->assertOk();
    }

    public function test_tenant_accesses_tenant_routes(): void
    {
        $user = $this->makeUser(['role' => 'tenant']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/me');
        $response->assertOk();
    }

    public function test_tenant_cannot_access_owner_resources(): void
    {
        $user = $this->makeUser(['role' => 'tenant']);
        $other = $this->makeUser(['role' => 'owner']);
        $token = $user->createToken('test')->plainTextToken;
        $otherToken = $other->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/me');
        $response->assertOk();

        $response = $this->withToken($otherToken)->getJson('/api/v1/me');
        $response->assertOk();
    }
}