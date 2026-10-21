<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AdvertisingCampaign;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * Boundary tests for the admin role completion work:
 *
 *   self access vs scoped operational access vs admin management vs
 *   super-admin access (see App\Policies\AdminPolicy).
 */
class AdminAuthorizationBoundaryTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $admin;

    private User $otherAdmin;

    private User $owner;

    private Kos $assignedKos;

    private Kos $foreignKos;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->otherAdmin = User::factory()->create(['role' => 'admin']);
        $this->owner = User::factory()->create(['role' => 'owner']);

        $this->assignedKos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->foreignKos = Kos::factory()->create(['owner_id' => $this->owner->id]);

        $this->admin->assignedKos()->attach($this->assignedKos->id);
    }

    // ─── TIER 1 — SELF ACCESS ─────────────────────────────────

    public function test_admin_can_view_self_via_policy(): void
    {
        $this->actingAs($this->admin);

        $this->assertTrue(Gate::allows('view', $this->admin));
    }

    public function test_admin_can_update_self_via_policy(): void
    {
        $this->actingAs($this->admin);

        $this->assertTrue(Gate::allows('update', $this->admin));
    }

    public function test_admin_can_update_own_profile_fields(): void
    {
        $response = $this->actingAs($this->admin)->patch(route('profile.update'), [
            'name' => 'Admin Updated',
            'email' => $this->admin->email,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('Admin Updated', $this->admin->fresh()->name);
    }

    public function test_admin_cannot_escalate_role_via_profile(): void
    {
        $response = $this->actingAs($this->admin)->patch(route('profile.update'), [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role' => 'super_admin',
            'is_active' => false,
        ]);

        $response->assertSessionHasErrors(['role', 'is_active']);
        $fresh = $this->admin->fresh();
        $this->assertSame('admin', $fresh->role);
        $this->assertTrue((bool) $fresh->is_active);
    }

    // ─── TIER 1 — ADMIN SELF-PROFILE VIA PROFILE CONTROLLER ──

    public function test_admin_self_profile_update_uses_authorize(): void
    {
        $response = $this->actingAs($this->admin)->patch(route('profile.update'), [
            'name' => 'Authorized Admin',
            'email' => $this->admin->email,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('Authorized Admin', $this->admin->fresh()->name);
    }

    public function test_admin_self_delete_authorized(): void
    {
        $response = $this->actingAs($this->admin)->delete('/profile', [
            'password' => 'password',
        ]);

        // Admin self-delete is allowed by policy; controller handles business logic
        $this->assertGuest();
    }

    // ─── TIER 3 — ADMIN-TO-ADMIN BOUNDARY ─────────────────────

    public function test_admin_cannot_view_another_admin_via_policy(): void
    {
        $this->actingAs($this->admin);

        $this->assertFalse(Gate::allows('view', $this->otherAdmin));
    }

    public function test_admin_cannot_view_another_admin_via_route(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.owners.show', $this->otherAdmin));
        $response->assertForbidden();
    }

    public function test_admin_cannot_update_another_admin_via_policy(): void
    {
        $this->actingAs($this->admin);

        $this->assertFalse(Gate::allows('update', $this->otherAdmin));
    }

    public function test_admin_cannot_toggle_active_another_admin_via_policy(): void
    {
        $this->actingAs($this->admin);

        $this->assertFalse(Gate::allows('toggleActive', $this->otherAdmin));
    }

    public function test_admin_cannot_toggle_active_self_via_policy(): void
    {
        $this->actingAs($this->admin);

        $this->assertFalse(Gate::allows('toggleActive', $this->admin));
    }

    // ─── TIER 3 — ASSIGNMENT AUTHORITY ────────────────────────

    public function test_admin_cannot_manage_assignment_via_policy(): void
    {
        $this->actingAs($this->admin);

        $this->assertFalse(Gate::allows('manageAssignment', $this->otherAdmin));
        $this->assertFalse(Gate::allows('manageAssignment', $this->admin));
    }

    public function test_super_admin_can_manage_assignment_via_policy(): void
    {
        $this->actingAs($this->superAdmin);

        $this->assertTrue(Gate::allows('manageAssignment', $this->admin));
    }

    public function test_admin_cannot_unassign_via_route(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('super-admin.admins.unassign', [$this->admin, $this->assignedKos]));

        $response->assertStatus(403);
        $this->assertTrue($this->admin->assignedKos()->where('kos_id', $this->assignedKos->id)->exists());
    }

    public function test_admin_cannot_assign_self_to_kos_via_kos_update(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.kos.update', $this->assignedKos), [
            'name' => $this->assignedKos->name,
            'address' => $this->assignedKos->address,
            'phone' => $this->assignedKos->phone ?? '081234567890',
            'admins' => [$this->otherAdmin->id],
        ]);

        $response->assertRedirect();
        $this->assertFalse(
            $this->otherAdmin->assignedKos()->where('kos_id', $this->assignedKos->id)->exists(),
            'Kos update must never change admin assignment for admin updaters.'
        );
    }

    // ─── TIER 4 — SUPER-ADMIN BOUNDARY ────────────────────────

    public function test_admin_cannot_view_super_admin_via_policy(): void
    {
        $this->actingAs($this->admin);

        $this->assertFalse(Gate::allows('view', $this->superAdmin));
        $this->assertFalse(Gate::allows('update', $this->superAdmin));
        $this->assertFalse(Gate::allows('toggleActive', $this->superAdmin));
        $this->assertFalse(Gate::allows('manageRole', $this->superAdmin));
    }

    public function test_super_admin_can_view_admin_via_policy(): void
    {
        $this->actingAs($this->superAdmin);

        $this->assertTrue(Gate::allows('view', $this->admin));
        $this->assertTrue(Gate::allows('update', $this->admin));
        $this->assertTrue(Gate::allows('manageAssignment', $this->admin));
    }

    // ─── KOS SCOPE ISOLATION BETWEEN ADMINS ───────────────────

    public function test_admin_cannot_access_other_admin_kos(): void
    {
        $otherKos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->otherAdmin->assignedKos()->attach($otherKos->id);

        $response = $this->actingAs($this->admin)->get(route('admin.kos.show', $otherKos));
        $response->assertForbidden();
    }

    // ─── AUDIT LOG SCOPE FILTER ───────────────────────────────

    public function test_admin_cannot_filter_audit_log_by_unassigned_kos(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.audit-log.index', ['kos_id' => $this->foreignKos->id]));

        $response->assertForbidden();
    }

    public function test_admin_can_filter_audit_log_by_assigned_kos(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.audit-log.index', ['kos_id' => $this->assignedKos->id]));

        $response->assertOk();
    }

    // ─── ADVERTISING CAMPAIGN VIEW SCOPE ──────────────────────

    public function test_admin_can_view_third_party_campaign(): void
    {
        $campaign = AdvertisingCampaign::factory()->thirdParty()->create();

        $this->actingAs($this->admin);

        $this->assertTrue(Gate::allows('view', $campaign));
    }

    public function test_admin_can_view_assigned_kos_campaign(): void
    {
        $campaign = AdvertisingCampaign::factory()->create(['kos_id' => $this->assignedKos->id]);

        $this->actingAs($this->admin);

        $this->assertTrue(Gate::allows('view', $campaign));
    }

    public function test_admin_cannot_view_unassigned_kos_campaign(): void
    {
        $campaign = AdvertisingCampaign::factory()->create(['kos_id' => $this->foreignKos->id]);

        $this->actingAs($this->admin);

        $this->assertFalse(Gate::allows('view', $campaign));
    }

    public function test_super_admin_can_view_any_campaign(): void
    {
        $campaign = AdvertisingCampaign::factory()->create(['kos_id' => $this->foreignKos->id]);

        $this->actingAs($this->superAdmin);

        $this->assertTrue(Gate::allows('view', $campaign));
    }

    // ─── ROLE ENUM VERIFICATION ─────────────────────────

    public function test_user_role_enum_values(): void
    {
        $this->assertSame('super_admin', UserRole::SUPER_ADMIN->value);
        $this->assertSame('admin', UserRole::ADMIN->value);
        $this->assertSame('owner', UserRole::OWNER->value);
        $this->assertSame('tenant', UserRole::TENANT->value);
    }

    public function test_user_role_enum_has_system_level_method(): void
    {
        $this->assertTrue(UserRole::SUPER_ADMIN->isSystemLevel());
        $this->assertFalse(UserRole::ADMIN->isSystemLevel());
    }

    public function test_user_role_enum_has_operational_manager_method(): void
    {
        $this->assertTrue(UserRole::ADMIN->isOperationalManager());
        $this->assertFalse(UserRole::SUPER_ADMIN->isOperationalManager());
    }

    public function test_admin_policy_registered_as_default_user_policy(): void
    {
        // AdminPolicy is registered as the default policy for User::class
        // in AuthServiceProvider. Verify that authorize() calls resolve
        // to AdminPolicy methods for admin-related operations.
        $this->actingAs($this->admin);

        $this->assertTrue(Gate::allows('view', $this->admin));
        $this->assertTrue(Gate::allows('update', $this->admin));
        $this->assertTrue(Gate::allows('delete', $this->admin));
        $this->assertFalse(Gate::allows('manageAssignment', $this->admin));
        $this->assertFalse(Gate::allows('manageRole', $this->admin));
    }

    public function test_admin_self_access_via_admin_policy_gate(): void
    {
        $this->actingAs($this->admin);

        $this->assertTrue(Gate::allows('view', $this->admin));
        $this->assertTrue(Gate::allows('update', $this->admin));
        $this->assertTrue(Gate::allows('delete', $this->admin));
    }
}
