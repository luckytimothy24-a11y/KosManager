<?php

namespace Tests\Feature;

use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModerationTest extends TestCase
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

    // ========== DASHBOARD ==========

    public function test_super_admin_can_access_moderation_dashboard(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/super-admin/moderation');
        $response->assertStatus(200);
    }

    public function test_moderation_dashboard_shows_pending_counts(): void
    {
        Kos::factory()->create(['status' => 'inactive', 'moderation_status' => 'pending']);
        Kos::factory()->create(['moderation_status' => 'flagged']);
        $response = $this->actingAs($this->superAdmin)->get('/super-admin/moderation');
        $response->assertStatus(200);
    }

    // ========== KOS MODERATION ==========

    public function test_super_admin_can_view_kos_moderation(): void
    {
        Kos::factory()->create(['status' => 'inactive', 'moderation_status' => 'pending']);
        $response = $this->actingAs($this->superAdmin)->get('/super-admin/moderation/kos');
        $response->assertOk();
    }

    public function test_super_admin_can_approve_kos(): void
    {
        $kos = Kos::factory()->create(['status' => 'inactive', 'moderation_status' => 'pending']);
        $this->actingAs($this->superAdmin)->post(route('super-admin.moderation.kos.approve', $kos));
        $this->assertDatabaseHas('kos', ['id' => $kos->id, 'moderation_status' => 'approved', 'status' => 'active']);
    }

    public function test_super_admin_can_reject_kos(): void
    {
        $kos = Kos::factory()->create(['status' => 'inactive', 'moderation_status' => 'pending']);
        $this->actingAs($this->superAdmin)->post(route('super-admin.moderation.kos.reject', $kos), [
            'reason' => 'Melanggar aturan platform',
        ]);
        $this->assertDatabaseHas('kos', ['id' => $kos->id, 'moderation_status' => 'rejected']);
    }

    // ========== DENIED ROLES ==========

    public function test_admin_cannot_access_moderation(): void
    {
        $response = $this->actingAs($this->admin)->get('/super-admin/moderation');
        $response->assertStatus(403);
    }

    public function test_owner_cannot_access_moderation(): void
    {
        $response = $this->actingAs($this->owner)->get('/super-admin/moderation');
        $response->assertStatus(403);
    }

    public function test_tenant_cannot_access_moderation(): void
    {
        $response = $this->actingAs($this->tenant)->get('/super-admin/moderation');
        $response->assertStatus(403);
    }

    // ========== IDOR ==========

    public function test_admin_cannot_approve_kos(): void
    {
        $kos = Kos::factory()->create(['status' => 'inactive', 'moderation_status' => 'pending']);
        $response = $this->actingAs($this->admin)->post(route('super-admin.moderation.kos.approve', $kos));
        $response->assertStatus(403);
    }

    public function test_owner_cannot_approve_kos(): void
    {
        $kos = Kos::factory()->create(['status' => 'inactive', 'moderation_status' => 'pending', 'owner_id' => $this->owner->id]);
        $response = $this->actingAs($this->owner)->post(route('super-admin.moderation.kos.approve', $kos));
        $response->assertStatus(403);
    }

    public function test_tenant_cannot_reject_kos(): void
    {
        $kos = Kos::factory()->create(['status' => 'inactive', 'moderation_status' => 'pending']);
        $response = $this->actingAs($this->tenant)->post(route('super-admin.moderation.kos.reject', $kos), [
            'reason' => 'Test',
        ]);
        $response->assertStatus(403);
    }
}
