<?php

namespace Tests\Feature;

use App\Models\CheckOut;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_with_login_link(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Masuk');
        $response->assertSee('href="'.route('login').'"', false);
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_super_admin_dashboard_renders(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Panduan Alur Kerja Super Admin');
        $response->assertSee('Pendapatan Masuk');
    }

    public function test_admin_dashboard_renders(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Panduan Alur Kerja Admin');
    }

    public function test_owner_dashboard_renders(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Panduan Alur Kerja Owner');
        $response->assertSee('Pendapatan Masuk');
    }

    public function test_tenant_dashboard_without_room_renders(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Belum Memiliki Kamar');
    }

    public function test_tenant_dashboard_with_active_room_renders(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);
        Penghuni::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Booking Saya');
        $response->assertSee('Tagihan Belum Bayar');
        $response->assertSee('Ajukan Check-Out');
    }

    public function test_tenant_dashboard_shows_pending_checkout_state(): void
    {
        $user = User::factory()->create(['role' => 'tenant']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
        ]);
        CheckOut::create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $penghuni->kamar_id,
            'request_date' => now(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Check-Out Diproses');
        $response->assertDontSee('Ajukan Check-Out');
    }
}
