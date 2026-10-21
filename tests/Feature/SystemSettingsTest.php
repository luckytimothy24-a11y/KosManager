<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $admin;

    private User $owner;

    private User $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
    }

    // ========== SUPER ADMIN ACCESS ==========

    public function test_super_admin_can_view_settings(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/super-admin/settings');
        $response->assertOk();
    }

    public function test_admin_cannot_view_settings(): void
    {
        $response = $this->actingAs($this->admin)->get('/super-admin/settings');
        $response->assertStatus(403);
    }

    public function test_owner_cannot_view_settings(): void
    {
        $response = $this->actingAs($this->owner)->get('/super-admin/settings');
        $response->assertStatus(403);
    }

    public function test_tenant_cannot_view_settings(): void
    {
        $response = $this->actingAs($this->tenant)->get('/super-admin/settings');
        $response->assertStatus(403);
    }

    public function test_settings_page_does_not_expose_secrets(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/super-admin/settings');
        $response->assertStatus(200);
        $response->assertDontSee('APP_KEY');
        $response->assertDontSee('DB_PASSWORD');
        $response->assertDontSee('MAIL_PASSWORD');
    }

    // ========== WHITELIST VALIDATION ==========

    public function test_arbitrary_setting_key_is_rejected(): void
    {
        $response = $this->actingAs($this->superAdmin)->put('/super-admin/settings', [
            'settings' => [
                ['key' => 'hack_key', 'value' => 'hacked'],
            ],
        ]);
        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('settings', ['key' => 'hack_key']);
    }
}
