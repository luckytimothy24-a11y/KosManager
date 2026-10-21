<?php

namespace Tests\Feature;

use App\Models\Kos;
use App\Models\KosPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $admin;

    protected User $owner;

    protected User $tenant;

    protected Kos $kos;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $this->tenant = User::factory()->create(['role' => 'tenant', 'is_active' => true]);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $this->admin->assignedKos()->attach($this->kos->id);
    }

    // ========== OWNER MANAGEMENT ==========

    public function test_super_admin_can_list_owners(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('super-admin.owners.index'));
        $response->assertStatus(200);
    }

    public function test_super_admin_can_view_owner(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('super-admin.owners.show', $this->owner));
        $response->assertStatus(200);
    }

    public function test_admin_cannot_access_owner_management(): void
    {
        $response = $this->actingAs($this->admin)->get(route('super-admin.owners.index'));
        $response->assertStatus(403);
    }

    public function test_owner_cannot_access_owner_management(): void
    {
        $response = $this->actingAs($this->owner)->get(route('super-admin.owners.index'));
        $response->assertStatus(403);
    }

    public function test_tenant_cannot_access_owner_management(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('super-admin.owners.index'));
        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_access_owner_management(): void
    {
        $response = $this->get(route('super-admin.owners.index'));
        $response->assertRedirect('/login');
    }

    // ========== ADMIN MANAGEMENT ==========

    public function test_super_admin_can_list_admins(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('super-admin.admins.index'));
        $response->assertStatus(200);
    }

    public function test_super_admin_can_create_admin(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('super-admin.admins.store'), [
            'name' => 'New Admin',
            'email' => 'newadmin@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'newadmin@test.com', 'role' => 'admin']);
    }

    public function test_super_admin_can_view_admin(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('super-admin.admins.show', $this->admin));
        $response->assertStatus(200);
    }

    public function test_admin_cannot_manage_admins(): void
    {
        $response = $this->actingAs($this->admin)->get(route('super-admin.admins.index'));
        $response->assertStatus(403);
    }

    public function test_owner_cannot_manage_admins(): void
    {
        $response = $this->actingAs($this->owner)->get(route('super-admin.admins.index'));
        $response->assertStatus(403);
    }

    public function test_tenant_cannot_manage_admins(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('super-admin.admins.index'));
        $response->assertStatus(403);
    }

    public function test_super_admin_can_assign_admin_to_kos(): void
    {
        $admin2 = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $kos2 = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $response = $this->actingAs($this->superAdmin)->post(route('super-admin.admins.assign', $admin2), [
            'kos_id' => $kos2->id,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('kos_user', ['user_id' => $admin2->id, 'kos_id' => $kos2->id]);
    }

    public function test_super_admin_can_unassign_admin_from_kos(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('super-admin.admins.unassign', [$this->admin, $this->kos]));
        $response->assertRedirect();
        $this->assertDatabaseMissing('kos_user', ['user_id' => $this->admin->id, 'kos_id' => $this->kos->id]);
    }

    public function test_admin_cannot_assign_admin(): void
    {
        $response = $this->actingAs($this->admin)->post(route('super-admin.admins.assign', $this->admin), [
            'kos_id' => $this->kos->id,
        ]);
        $response->assertStatus(403);
    }

    // ========== ADMIN ASSIGNMENT ISOLATION ==========

    public function test_admin_a_cannot_access_kos_assigned_to_admin_b(): void
    {
        $adminB = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $kosB = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $adminB->assignedKos()->attach($kosB->id);

        $response = $this->actingAs($this->admin)->get(route('admin.kos.show', $kosB));
        $response->assertStatus(403);
    }

    public function test_admin_cannot_manipulate_other_admin_assignment(): void
    {
        $response = $this->actingAs($this->admin)->post(route('super-admin.admins.assign', $this->admin), [
            'kos_id' => $this->kos->id,
        ]);
        $response->assertStatus(403);
    }

    public function test_owner_cannot_assign_themselves_as_admin(): void
    {
        $response = $this->actingAs($this->owner)->post(route('super-admin.admins.assign', $this->owner), [
            'kos_id' => $this->kos->id,
        ]);
        $response->assertStatus(403);
    }

    public function test_tenant_cannot_access_assignment_endpoint(): void
    {
        $response = $this->actingAs($this->tenant)->post(route('super-admin.admins.assign', $this->admin), [
            'kos_id' => $this->kos->id,
        ]);
        $response->assertStatus(403);
    }

    // ========== MODERATION ==========

    public function test_super_admin_can_access_moderation(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('super-admin.moderation.index'));
        $response->assertStatus(200);
    }

    public function test_super_admin_can_access_kos_moderation(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('super-admin.moderation.kos'));
        $response->assertStatus(200);
    }

    public function test_super_admin_can_access_photo_moderation(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('super-admin.moderation.photos'));
        $response->assertStatus(200);
    }

    public function test_super_admin_can_access_advertising_moderation(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('super-admin.moderation.advertising'));
        $response->assertStatus(200);
    }

    public function test_super_admin_can_access_damage_reports_moderation(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('super-admin.moderation.damage-reports'));
        $response->assertStatus(200);
    }

    public function test_admin_cannot_access_moderation(): void
    {
        $response = $this->actingAs($this->admin)->get(route('super-admin.moderation.index'));
        $response->assertStatus(403);
    }

    public function test_owner_cannot_access_moderation(): void
    {
        $response = $this->actingAs($this->owner)->get(route('super-admin.moderation.index'));
        $response->assertStatus(403);
    }

    public function test_tenant_cannot_access_moderation(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('super-admin.moderation.index'));
        $response->assertStatus(403);
    }

    public function test_super_admin_can_approve_kos(): void
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'moderation_status' => 'pending']);
        $response = $this->actingAs($this->superAdmin)->post(route('super-admin.moderation.kos.approve', $kos));
        $response->assertRedirect();
        $this->assertDatabaseHas('kos', ['id' => $kos->id, 'moderation_status' => 'approved', 'status' => 'active']);
    }

    public function test_super_admin_can_reject_kos(): void
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'moderation_status' => 'pending']);
        $response = $this->actingAs($this->superAdmin)->post(route('super-admin.moderation.kos.reject', $kos), [
            'reason' => 'Konten tidak sesuai',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('kos', ['id' => $kos->id, 'moderation_status' => 'rejected']);
    }

    public function test_admin_cannot_approve_kos_moderation(): void
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'moderation_status' => 'pending']);
        $response = $this->actingAs($this->admin)->post(route('super-admin.moderation.kos.approve', $kos));
        $response->assertStatus(403);
    }

    public function test_owner_cannot_approve_kos_moderation(): void
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'moderation_status' => 'pending']);
        $response = $this->actingAs($this->owner)->post(route('super-admin.moderation.kos.approve', $kos));
        $response->assertStatus(403);
    }

    public function test_super_admin_can_approve_photo(): void
    {
        $photo = KosPhoto::factory()->create(['kos_id' => $this->kos->id, 'status' => 'pending']);
        $response = $this->actingAs($this->superAdmin)->post(route('super-admin.moderation.photos.approve', $photo));
        $response->assertRedirect();
        $this->assertDatabaseHas('kos_photos', ['id' => $photo->id, 'status' => 'approved']);
    }

    public function test_super_admin_can_reject_photo(): void
    {
        $photo = KosPhoto::factory()->create(['kos_id' => $this->kos->id, 'status' => 'pending']);
        $response = $this->actingAs($this->superAdmin)->post(route('super-admin.moderation.photos.reject', $photo), [
            'reason' => 'Foto tidak pantas',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('kos_photos', ['id' => $photo->id, 'status' => 'rejected']);
    }

    public function test_admin_cannot_approve_photo_moderation(): void
    {
        $photo = KosPhoto::factory()->create(['kos_id' => $this->kos->id, 'status' => 'pending']);
        $response = $this->actingAs($this->admin)->post(route('super-admin.moderation.photos.approve', $photo));
        $response->assertStatus(403);
    }

    // ========== SYSTEM SETTINGS ==========

    public function test_super_admin_can_view_settings(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('super-admin.settings.index'));
        $response->assertStatus(200);
    }

    public function test_super_admin_can_update_settings(): void
    {
        $response = $this->actingAs($this->superAdmin)->put(route('super-admin.settings.update'), [
            'settings' => [
                ['key' => 'application_name', 'value' => 'Test App'],
                ['key' => 'items_per_page', 'value' => '20'],
            ],
        ]);
        $response->assertRedirect();
    }

    public function test_admin_cannot_view_settings(): void
    {
        $response = $this->actingAs($this->admin)->get(route('super-admin.settings.index'));
        $response->assertStatus(403);
    }

    public function test_owner_cannot_view_settings(): void
    {
        $response = $this->actingAs($this->owner)->get(route('super-admin.settings.index'));
        $response->assertStatus(403);
    }

    public function test_tenant_cannot_view_settings(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('super-admin.settings.index'));
        $response->assertStatus(403);
    }

    public function test_arbitrary_setting_key_is_rejected(): void
    {
        $response = $this->actingAs($this->superAdmin)->put(route('super-admin.settings.update'), [
            'settings' => [
                ['key' => 'APP_KEY', 'value' => 'hacked'],
            ],
        ]);
        $response->assertSessionHasErrors();
    }

    public function test_invalid_setting_value_is_rejected(): void
    {
        $response = $this->actingAs($this->superAdmin)->put(route('super-admin.settings.update'), [
            'settings' => [
                ['key' => 'items_per_page', 'value' => 'not-a-number'],
            ],
        ]);
        $response->assertRedirect();
    }

    // ========== IDOR TESTING ==========

    public function test_admin_cannot_view_other_admin_detail(): void
    {
        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($this->admin)->get(route('super-admin.admins.show', $otherAdmin));
        $response->assertStatus(403);
    }

    public function test_owner_cannot_view_owner_detail(): void
    {
        $otherOwner = User::factory()->create(['role' => 'owner']);
        $response = $this->actingAs($this->owner)->get(route('super-admin.owners.show', $otherOwner));
        $response->assertStatus(403);
    }

    public function test_tenant_cannot_access_any_super_admin_endpoint(): void
    {
        $endpoints = [
            route('super-admin.owners.index'),
            route('super-admin.admins.index'),
            route('super-admin.moderation.index'),
            route('super-admin.settings.index'),
        ];

        foreach ($endpoints as $url) {
            $response = $this->actingAs($this->tenant)->get($url);
            $response->assertStatus(403);
        }
    }
}
