<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\KosPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminScopedCapabilityTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $admin;

    private User $otherAdmin;

    private User $owner;

    private User $tenant;

    private Kos $assignedKos;

    private Kos $foreignKos;

    private Kamar $assignedKamar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->otherAdmin = User::factory()->create(['role' => 'admin']);
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);

        $this->assignedKos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->foreignKos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->assignedKamar = Kamar::factory()->create(['kos_id' => $this->assignedKos->id, 'status' => 'available']);

        $this->admin->assignedKos()->attach($this->assignedKos->id);
    }

    // ─── USER POLICY — ADMIN SCOPED ──────────────────────

    public function test_admin_can_view_assigned_owner(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.owners.show', $this->owner));
        $response->assertOk();
    }

    public function test_admin_cannot_view_unrelated_owner(): void
    {
        $unrelatedOwner = User::factory()->create(['role' => 'owner']);
        $response = $this->actingAs($this->admin)->get(route('admin.owners.show', $unrelatedOwner));
        $response->assertForbidden();
    }

    public function test_admin_cannot_access_super_admin_user_management(): void
    {
        $response = $this->actingAs($this->admin)->get(route('super-admin.users.index'));
        $response->assertStatus(403);
    }

    public function test_admin_cannot_create_user(): void
    {
        $response = $this->actingAs($this->admin)->get(route('super-admin.users.create'));
        $response->assertStatus(403);
    }

    public function test_admin_cannot_delete_user(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('super-admin.users.destroy', $this->owner));
        $response->assertForbidden();
    }

    public function test_admin_cannot_toggle_active_another_admin(): void
    {
        $response = $this->actingAs($this->admin)->post(route('super-admin.users.toggle-active', $this->otherAdmin));
        $response->assertForbidden();
    }

    public function test_admin_cannot_toggle_active_super_admin(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $response = $this->actingAs($this->admin)->post(route('super-admin.users.toggle-active', $superAdmin));
        $response->assertForbidden();
    }

    public function test_admin_can_view_owner_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.owners.index'));
        $response->assertOk();
    }

    // ─── MODERATION — ADMIN SCOPED ───────────────────────

    public function test_admin_can_moderate_assigned_kos(): void
    {
        $kos = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'moderation_status' => 'pending',
        ]);
        $this->admin->assignedKos()->attach($kos->id);

        $response = $this->actingAs($this->admin)->post(route('admin.moderation.kos.approve', $kos));
        $response->assertRedirect();
    }

    public function test_admin_cannot_moderate_unassigned_kos(): void
    {
        $kos = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'moderation_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.moderation.kos.approve', $kos));
        $response->assertForbidden();
    }

    public function test_super_admin_can_moderate_any_kos(): void
    {
        $kos = Kos::factory()->create(['moderation_status' => 'pending', 'status' => 'inactive']);

        $response = $this->actingAs($this->superAdmin)->post(route('admin.moderation.kos.approve', $kos));
        $response->assertRedirect();
    }

    public function test_admin_can_reject_assigned_kos(): void
    {
        $kos = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'moderation_status' => 'pending',
        ]);
        $this->admin->assignedKos()->attach($kos->id);

        $response = $this->actingAs($this->admin)->post(route('admin.moderation.kos.reject', $kos), ['reason' => 'Not valid']);
        $response->assertRedirect();
    }

    public function test_admin_can_moderate_assigned_photos(): void
    {
        $photo = KosPhoto::factory()->create([
            'kos_id' => $this->assignedKos->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.moderation.photos.approve', $photo));
        $response->assertRedirect();
    }

    public function test_admin_cannot_moderate_unassigned_photos(): void
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $photo = KosPhoto::factory()->create(['kos_id' => $kos->id, 'status' => 'pending']);

        $response = $this->actingAs($this->admin)->post(route('admin.moderation.photos.approve', $photo));
        $response->assertForbidden();
    }

    public function test_admin_can_view_moderation_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.moderation.index'));
        $response->assertOk();
    }

    public function test_admin_can_view_moderation_kos(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.moderation.kos'));
        $response->assertOk();
    }

    public function test_admin_can_view_moderation_photos(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.moderation.photos'));
        $response->assertOk();
    }

    public function test_admin_can_view_moderation_advertising(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.moderation.advertising'));
        $response->assertOk();
    }

    public function test_admin_can_view_moderation_damage_reports(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.moderation.damage-reports'));
        $response->assertOk();
    }

    // ─── AUDIT LOG — ADMIN SCOPED ────────────────────────

    public function test_admin_can_view_audit_log(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.audit-log.index'));
        $response->assertOk();
    }

    public function test_super_admin_can_view_all_audit_logs(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.audit-log.index'));
        $response->assertOk();
    }

    public function test_admin_cannot_access_super_admin_settings(): void
    {
        $response = $this->actingAs($this->admin)->get(route('super-admin.settings.index'));
        $response->assertStatus(403);
    }

    // ─── OWNER MANAGEMENT — ADMIN SCOPED ─────────────────

    public function test_admin_can_view_owner_detail(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.owners.show', $this->owner));
        $response->assertOk();
    }

    public function test_admin_cannot_view_unrelated_owner_detail(): void
    {
        $unrelatedOwner = User::factory()->create(['role' => 'owner']);
        $response = $this->actingAs($this->admin)->get(route('admin.owners.show', $unrelatedOwner));
        $response->assertForbidden();
    }

    // ─── KOS CREATION SECURITY ────────────────────────────

    public function test_admin_can_create_kos_for_assigned_owner(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.kos.store'), [
            'name' => 'New Kos Admin',
            'address' => 'Jl. Test',
            'phone' => '081234567890',
            'status' => 'active',
            'owner_id' => $this->owner->id,
            'fasilitas' => [],
        ]);
        $response->assertRedirect(route('admin.kos.index'));
    }

    public function test_admin_cannot_create_kos_for_unrelated_owner(): void
    {
        $unrelatedOwner = User::factory()->create(['role' => 'owner']);
        $response = $this->actingAs($this->admin)->post(route('admin.kos.store'), [
            'name' => 'Hacked Kos',
            'address' => 'Jl. Hack',
            'phone' => '081234567890',
            'status' => 'active',
            'owner_id' => $unrelatedOwner->id,
            'fasilitas' => [],
        ]);
        $response->assertForbidden();
    }

    // ─── PRIVILEGE ESCALATION FORBIDDEN ──────────────────

    public function test_admin_cannot_create_admin(): void
    {
        $response = $this->actingAs($this->admin)->get(route('super-admin.admins.create'));
        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_system_settings(): void
    {
        $response = $this->actingAs($this->admin)->get(route('super-admin.settings.index'));
        $response->assertStatus(403);
    }

    public function test_admin_cannot_manage_advertising_packages(): void
    {
        $response = $this->actingAs($this->admin)->get(route('super-admin.advertising.packages.index'));
        $response->assertStatus(403);
    }

    public function test_admin_cannot_manage_partners(): void
    {
        $response = $this->actingAs($this->admin)->get(route('super-admin.partners.index'));
        $response->assertStatus(403);
    }

    public function test_admin_cannot_assign_kos_to_another_admin(): void
    {
        $response = $this->actingAs($this->admin)->post(route('super-admin.admins.assign', $this->otherAdmin), [
            'kos_id' => $this->assignedKos->id,
        ]);
        $response->assertStatus(403);
    }

    // ─── IDOR TESTS ──────────────────────────────────────

    public function test_admin_assigned_kos_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kos.show', $this->assignedKos));
        $response->assertOk();
    }

    public function test_admin_unassigned_kos_forbidden(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kos.show', $this->foreignKos));
        $response->assertForbidden();
    }

    public function test_admin_assigned_kamar_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kamar.show', $this->assignedKamar));
        $response->assertOk();
    }

    public function test_admin_unassigned_kamar_forbidden(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->foreignKos->id]);
        $response = $this->actingAs($this->admin)->get(route('admin.kamar.show', $kamar));
        $response->assertForbidden();
    }

    public function test_admin_assigned_booking_accessible(): void
    {
        $booking = Booking::factory()->create(['kos_id' => $this->assignedKos->id]);
        $response = $this->actingAs($this->admin)->get(route('admin.booking.show', $booking));
        $response->assertOk();
    }

    public function test_admin_unassigned_booking_forbidden(): void
    {
        $booking = Booking::factory()->create(['kos_id' => $this->foreignKos->id]);
        $response = $this->actingAs($this->admin)->get(route('admin.booking.show', $booking));
        $response->assertForbidden();
    }

    public function test_admin_cannot_create_admin_via_user_policy(): void
    {
        $response = $this->actingAs($this->admin)->get(route('super-admin.users.create'));
        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_unassigned_kos_via_policy(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kos.show', $this->foreignKos));
        $response->assertForbidden();
    }

    public function test_super_admin_retains_global_kos_access(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.kos.show', $this->foreignKos));
        $response->assertOk();
    }
}
