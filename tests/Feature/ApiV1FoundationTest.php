<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiV1FoundationTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge(['password' => Hash::make('secret123')], $overrides));
    }

    // ---------------------------------------------------------------- login

    public function test_login_returns_token_and_public_profile(): void
    {
        $user = $this->makeUser();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'role'],
                ],
            ]);

        $body = $response->json();
        $this->assertSame('Bearer', $body['data']['token_type']);
        $this->assertSame($user->email, $body['data']['user']['email']);
        $this->assertArrayNotHasKey('password', $body['data']['user']);
        $this->assertArrayNotHasKey('remember_token', $body['data']['user']);
        $this->assertSame(1, DB::table('personal_access_tokens')->count());
    }

    public function test_login_rejects_invalid_credentials_without_creating_token(): void
    {
        $user = $this->makeUser();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
        $response->assertJsonMissingPath('data.access_token');
        $this->assertSame(0, DB::table('personal_access_tokens')->count());
    }

    public function test_login_rejects_inactive_user(): void
    {
        $user = $this->makeUser(['is_active' => false]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertStatus(401);
        $this->assertSame(0, DB::table('personal_access_tokens')->count());
    }

    public function test_inactive_user_with_valid_token_gets_403_json_not_500(): void
    {
        $user = $this->makeUser(['role' => 'tenant']);
        $token = $user->createToken('api')->plainTextToken;

        $user->update(['is_active' => false]);
        app('auth')->forgetGuards();

        $response = $this->withToken($token)->getJson('/api/v1/favorites');

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.']);
    }

    public function test_login_validates_input(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['email', 'password']]);
        $this->assertSame(0, DB::table('personal_access_tokens')->count());
    }

    public function test_api_login_does_not_create_web_session(): void
    {
        $user = $this->makeUser();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertOk();

        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_web_login_flow_is_unaffected(): void
    {
        $user = $this->makeUser();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->assertSame(0, DB::table('personal_access_tokens')->count());
    }

    // ---------------------------------------------------------------- me

    public function test_me_returns_authenticated_user_from_token(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/me');

        $response->assertOk();
        $body = $response->json();
        $this->assertSame($user->id, $body['data']['id']);
        $this->assertSame($user->email, $body['data']['email']);
        $this->assertArrayNotHasKey('password', $body['data']);
        $this->assertArrayNotHasKey('remember_token', $body['data']);
    }

    public function test_me_ignores_user_id_from_request(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/me?user_id='.$other->id);

        $response->assertOk();
        $this->assertSame($user->id, $response->json('data.id'));
        $this->assertSame($user->email, $response->json('data.email'));
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/v1/me')->assertStatus(401);
    }

    public function test_me_rejects_token_revoked_by_logout(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('session')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();

        app('auth')->forgetGuards();

        $this->withToken($token)->getJson('/api/v1/me')->assertStatus(401);
    }

    // ---------------------------------------------------------------- logout

    public function test_logout_revokes_only_the_current_token(): void
    {
        $user = $this->makeUser();
        $tokenA = $user->createToken('device-a');
        $tokenB = $user->createToken('device-b');

        $response = $this->withToken($tokenA->plainTextToken)
            ->postJson('/api/v1/auth/logout');

        $response->assertOk();
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenA->accessToken->id]);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $tokenB->accessToken->id]);

        app('auth')->forgetGuards();

        $this->withToken($tokenA->plainTextToken)->getJson('/api/v1/me')->assertStatus(401);
        $this->withToken($tokenB->plainTextToken)->getJson('/api/v1/me')->assertOk();
    }

    public function test_logout_does_not_affect_web_session(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('app')->plainTextToken;

        $this->actingAs($user)->get('/dashboard')->assertOk();

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();

        $this->get('/dashboard')->assertOk();
        $this->assertAuthenticated();
    }

    public function test_logout_requires_authentication(): void
    {
        $this->postJson('/api/v1/auth/logout')->assertStatus(401);
    }

    // ---------------------------------------------------------------- health

    public function test_health_is_public_and_ok(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertOk()
            ->assertJson([
                'status' => 'ok',
                'checks' => ['database' => 'connected'],
            ]);
    }

    public function test_health_returns_503_without_leaking_details_when_database_is_down(): void
    {
        config(['database.default' => 'broken_sqlite_api']);
        config(['database.connections.broken_sqlite_api' => [
            'driver' => 'sqlite',
            'database' => base_path('nonexistent-dir/db.sqlite'),
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]]);
        DB::purge('broken_sqlite_api');

        try {
            $response = $this->getJson('/api/v1/health');

            $response->assertStatus(503)
                ->assertJson([
                    'status' => 'error',
                    'checks' => ['database' => 'unavailable'],
                ]);
            $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
            $this->assertStringNotContainsString('nonexistent-dir', $response->getContent());
            $this->assertStringNotContainsString(config('app.key'), $response->getContent());
        } finally {
            config(['database.default' => 'sqlite']);
            config(['database.connections.broken_sqlite_api' => null]);
            DB::purge('broken_sqlite_api');
        }
    }

    public function test_health_does_not_expose_environment_details(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringNotContainsString('DB_PASSWORD', $content);
        $this->assertStringNotContainsString('APP_KEY', $content);
        $this->assertStringNotContainsString('APP_SECRET', $content);
        $this->assertStringNotContainsString('MAIL_PASSWORD', $content);
    }
}
