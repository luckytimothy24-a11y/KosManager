<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminProductivityTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    // ========== S1 TOGGLE ACTIVE ==========

    public function test_super_admin_can_toggle_user_active(): void
    {
        $sa = $this->superAdmin();
        $tenant = User::factory()->create(['role' => 'tenant', 'is_active' => true]);

        $this->actingAs($sa)->post(route('super-admin.users.toggle-active', $tenant))
            ->assertRedirect();
        $this->assertFalse($tenant->fresh()->is_active);

        $this->actingAs($sa)->post(route('super-admin.users.toggle-active', $tenant))
            ->assertRedirect();
        $this->assertTrue($tenant->fresh()->is_active);
    }

    public function test_cannot_toggle_self_or_last_super_admin(): void
    {
        $sa = $this->superAdmin();

        $this->actingAs($sa)->post(route('super-admin.users.toggle-active', $sa))
            ->assertStatus(400);
        $this->assertTrue($sa->fresh()->is_active);
    }

    public function test_admin_cannot_toggle_users(): void
    {
        $sa = $this->superAdmin();
        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($admin)->post(route('super-admin.users.toggle-active', $tenant))
            ->assertForbidden();
        $this->actingAs($admin)->post(route('super-admin.users.reset-password', $tenant))
            ->assertForbidden();
    }

    // ========== S1 RESET PASSWORD ==========

    public function test_reset_password_changes_hash_and_shows_once(): void
    {
        $sa = $this->superAdmin();
        $tenant = User::factory()->create(['role' => 'tenant']);
        $oldHash = $tenant->password;

        $response = $this->actingAs($sa)->post(route('super-admin.users.reset-password', $tenant));
        $response->assertRedirect();
        $response->assertSessionHas('new_password');

        $plain = session('new_password');
        $this->assertNotEmpty($plain);
        $this->assertNotEquals($oldHash, $tenant->fresh()->password);
        $this->assertTrue(Hash::check($plain, $tenant->fresh()->password));

        // Password baru tidak masuk audit log.
        $this->assertDatabaseMissing('audit_logs', ['description' => $plain]);
    }

    // ========== S2 EXPORT ==========

    public function test_export_csv_respects_filters(): void
    {
        $sa = $this->superAdmin();
        User::factory()->create(['role' => 'tenant', 'name' => 'Tenant Export']);
        User::factory()->create(['role' => 'owner', 'name' => 'Owner Export']);

        $response = $this->actingAs($sa)->get(route('super-admin.users.export', ['role' => 'tenant']));
        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('Tenant Export', $content);
        $this->assertStringNotContainsString('Owner Export', $content);
        $this->assertStringContainsString('ID,Nama,Email', $content);
        $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('Content-Type'));
    }

    public function test_non_super_admin_cannot_export(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)->get(route('super-admin.users.export'))->assertForbidden();
    }

    // ========== S3 HEALTH ==========

    public function test_health_page_renders_without_secrets(): void
    {
        $sa = $this->superAdmin();

        $response = $this->actingAs($sa)->get(route('super-admin.system-health.index'));
        $response->assertOk();
        $response->assertSee('Kesehatan Sistem');
        $response->assertSee('Database');

        $content = strtolower($response->getContent());
        foreach (['db_password', 'secret', 'aws_secret', '.env'] as $needle) {
            $this->assertStringNotContainsString($needle, $content, "Bocor: {$needle}");
        }
    }

    public function test_health_page_denies_other_roles(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($owner)->get(route('super-admin.system-health.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('super-admin.system-health.index'))->assertForbidden();
        $this->actingAs($tenant)->get(route('super-admin.system-health.index'))->assertForbidden();
    }
}
