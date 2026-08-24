<?php

namespace Tests\Feature;

use App\Models\Fasilitas;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_super_admin_pages_render(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        Kos::factory()->count(2)->create();
        Fasilitas::factory()->count(3)->create();

        $routes = [
            ['GET', '/super-admin/users'],
            ['GET', '/super-admin/users/create'],
            ['GET', '/super-admin/users/1/edit'],
            ['GET', '/super-admin/fasilitas'],
            ['GET', '/super-admin/fasilitas/create'],
            ['GET', '/super-admin/fasilitas/1/edit'],
            ['GET', '/super-admin/laporan'],
            ['GET', '/super-admin/audit-log'],
            ['GET', '/super-admin/laporan/export-pdf/kamar'],
            ['GET', '/super-admin/laporan/export-excel/kamar'],
            ['GET', '/super-admin/laporan/export-pdf/penghuni'],
            ['GET', '/super-admin/laporan/export-pdf/pendapatan'],
            ['GET', '/super-admin/laporan/export-pdf/booking'],
            ['GET', '/super-admin/laporan/export-pdf/tagihan'],
            ['GET', '/super-admin/laporan/export-excel/pendapatan'],
            ['GET', '/super-admin/laporan/export-excel/penghuni'],
            ['GET', '/super-admin/laporan/export-excel/booking'],
            ['GET', '/super-admin/laporan/export-excel/tagihan'],
        ];

        foreach ($routes as [$method, $uri]) {
            $response = $this->actingAs($admin)->get($uri);
            if ($response->status() >= 400) {
                fwrite(STDERR, "FAILED: {$method} {$uri} => HTTP {$response->status()}\n");
                fwrite(STDERR, $response->exception ? $response->exception->getMessage()."\n" : '');
            }
            $response->assertStatus(200);
        }
    }

    public function test_super_admin_user_crud_works(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $store = $this->actingAs($admin)->post('/super-admin/users', [
            'name' => 'Test Owner',
            'email' => 'owner@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'owner',
            'phone' => '081234567890',
        ]);
        $store->assertRedirect('/super-admin/users');

        $user = User::where('email', 'owner@test.com')->firstOrFail();

        $update = $this->actingAs($admin)->put("/super-admin/users/{$user->id}", [
            'name' => 'Test Owner Updated',
            'email' => 'owner@test.com',
            'role' => 'owner',
            'phone' => '081234567891',
            'is_active' => '1',
        ]);
        $update->assertRedirect('/super-admin/users');

        $delete = $this->actingAs($admin)->delete("/super-admin/users/{$user->id}");
        $delete->assertRedirect('/super-admin/users');
    }

    public function test_super_admin_fasilitas_crud_works(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $store = $this->actingAs($admin)->post('/super-admin/fasilitas', [
            'name' => 'Kolam Renang',
            'icon' => 'ri-water-flash-line',
        ]);
        $store->assertRedirect('/super-admin/fasilitas');

        $fasilitas = Fasilitas::where('name', 'Kolam Renang')->firstOrFail();

        $update = $this->actingAs($admin)->put("/super-admin/fasilitas/{$fasilitas->id}", [
            'name' => 'Kolam Renang Updated',
            'icon' => 'ri-water-flash-line',
        ]);
        $update->assertRedirect('/super-admin/fasilitas');

        $delete = $this->actingAs($admin)->delete("/super-admin/fasilitas/{$fasilitas->id}");
        $delete->assertRedirect('/super-admin/fasilitas');
    }
}
