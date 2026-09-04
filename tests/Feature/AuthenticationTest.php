<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_users_can_login_using_correct_credentials(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_cannot_login_using_wrong_credentials(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_failed_login_preserves_email_and_shows_error_feedback(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
            'email' => 'owner@example.com',
        ]);

        $response = $this->from('login')->post('/login', [
            'email' => 'owner@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('login');
        $response->assertSessionHasErrors('email');
        $response->assertSessionHasInput('email', 'owner@example.com');
        $this->assertGuest();

        $this->get('login')
            ->assertSee('owner@example.com')
            ->assertSee('Email atau kata sandi tidak sesuai.');
    }

    public function test_login_is_recorded_in_audit_log(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'Login',
            'module' => 'Auth',
        ]);
    }

    public function test_logout_is_recorded_in_audit_log(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $this->actingAs($user)->post('/logout');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'Logout',
            'module' => 'Auth',
        ]);
    }

    public function test_failed_login_is_not_recorded_in_audit_log(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertDatabaseMissing('audit_logs', [
            'user_id' => $user->id,
            'action' => 'Login',
        ]);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create([
            'role' => 'owner',
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');
        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_user_is_redirected_to_dashboard_by_role(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
    }
}
