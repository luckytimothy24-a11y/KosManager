<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeekerLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeker_can_register_then_login(): void
    {
        $this->post(route('register'), [
            'name' => 'Pencari Kos',
            'email' => 'pencari@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertSame('tenant', auth()->user()->refresh()->role);

        $this->post(route('logout'))->assertRedirect('/');

        $this->post(route('login'), [
            'email' => 'pencari@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs(User::where('email', 'pencari@example.com')->first());
    }

    public function test_login_ignores_email_case_and_spaces(): void
    {
        User::factory()->create([
            'email' => 'pencari@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->post(route('login'), [
            'email' => '  PENCARI@Example.COM  ',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_login_locks_out_after_five_failures(): void
    {
        User::factory()->create(['email' => 'pencari@example.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), [
                'email' => 'pencari@example.com',
                'password' => 'salah',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login'), [
            'email' => 'pencari@example.com',
            'password' => 'salah',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_deactivated_account_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'pencari@example.com',
            'password' => bcrypt('password123'),
            'is_active' => false,
        ]);

        $this->post(route('login'), [
            'email' => 'pencari@example.com',
            'password' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
