<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileBottomNavTest extends TestCase
{
    use RefreshDatabase;

    private function assertBottomNavVisible(User $user): void
    {
        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('aria-label="Navigasi utama"', false);
        $response->assertSee('lg:hidden fixed bottom-0', false);
        $response->assertSee('bg-primary-50', false);
        $response->assertSee('Profil');
    }

    public function test_tenant_bottom_nav_contains_tenant_menu(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->assertBottomNavVisible($tenant);

        $this->actingAs($tenant)
            ->get(route('dashboard'))
            ->assertSee(route('tenant.favorites.index'), false)
            ->assertSee(route('tenant.booking.index'), false)
            ->assertSee(route('tenant.tagihan.index'), false)
            ->assertSee('Favorit');
    }

    public function test_owner_bottom_nav_contains_owner_menu(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->assertBottomNavVisible($owner);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertSee(route('owner.kos.index'), false)
            ->assertSee(route('owner.booking.index'), false)
            ->assertSee(route('owner.tagihan.index'), false)
            ->assertSee('Kos');
    }

    public function test_admin_bottom_nav_contains_admin_menu(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertBottomNavVisible($admin);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertSee(route('admin.kos.index'), false)
            ->assertSee(route('admin.booking.index'), false)
            ->assertSee(route('admin.tagihan.index'), false)
            ->assertSee('Kos');
    }

    public function test_super_admin_bottom_nav_contains_platform_menu(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->assertBottomNavVisible($superAdmin);

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertSee(route('owner.kos.index'), false)
            ->assertSee(route('owner.booking.index'), false)
            ->assertSee(route('owner.tagihan.index'), false)
            ->assertSee('Kos');
    }
}
