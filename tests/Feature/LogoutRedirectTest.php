<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LogoutRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_redirects_to_landing_with_status(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $response->assertSessionHas('status', 'Anda telah berhasil keluar.');
    }

    public function test_login_ignores_stale_intended_url_from_other_role(): void
    {
        // Skenario: session sebelumnya menyimpan URL milik owner, lalu user login sebagai admin.
        // Post-login wajib mendarat di dashboard (sesuai role), bukan 403 di URL owner.
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        $response = $this->withSession(['url' => ['intended' => '/owner/kos']])
            ->post('/login', [
                'email' => $admin->email,
                'password' => 'password',
            ]);

        $response->assertRedirect(route('dashboard'));
        $this->followingRedirects()->get('/dashboard')->assertOk();
    }
}
