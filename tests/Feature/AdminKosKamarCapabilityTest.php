<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminKosKamarCapabilityTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $admin;
    private User $foreignAdmin;
    private User $tenant;
    private Kos $assignedKos;
    private Kos $foreignKos;
    private Kamar $assignedKamar;
    private Kamar $foreignKamar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->foreignAdmin = User::factory()->create(['role' => 'admin']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);

        $this->assignedKos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->foreignKos = Kos::factory()->create(['owner_id' => $this->owner->id]);

        $this->assignedKos->admins()->attach($this->admin->id);

        $this->assignedKamar = Kamar::factory()->create([
            'kos_id' => $this->assignedKos->id,
            'status' => 'available',
            'capacity' => 1,
        ]);
        $this->foreignKamar = Kamar::factory()->create([
            'kos_id' => $this->foreignKos->id,
            'status' => 'available',
            'capacity' => 1,
        ]);
    }

    // ─── KOS READ ──────────────────────────────────────────

    public function test_admin_assigned_can_view_kos_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kos.index'));
        $response->assertOk();
        $response->assertSee($this->assignedKos->name);
        $response->assertDontSee($this->foreignKos->name);
    }

    public function test_admin_assigned_can_view_kos_show(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kos.show', $this->assignedKos));
        $response->assertOk();
        $response->assertSee($this->assignedKos->name);
    }

    public function test_admin_foreign_kos_denied_show(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kos.show', $this->foreignKos));
        $response->assertForbidden();
    }

    public function test_admin_empty_assignment_sees_no_kos(): void
    {
        $response = $this->actingAs($this->foreignAdmin)->get(route('admin.kos.index'));
        $response->assertOk();
        $response->assertDontSee($this->assignedKos->name);
        $response->assertDontSee($this->foreignKos->name);
    }

    public function test_tenant_cannot_access_admin_kos_routes(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('admin.kos.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->tenant)->get(route('admin.kos.show', $this->assignedKos));
        $response->assertStatus(403);
    }

    // ─── KAMAR READ ────────────────────────────────────────

    public function test_admin_assigned_can_view_kamar_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kamar.index'));
        $response->assertOk();
        $response->assertSee($this->assignedKamar->room_number);
        $response->assertDontSee($this->foreignKamar->room_number);
    }

    public function test_admin_assigned_can_view_kamar_show(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kamar.show', $this->assignedKamar));
        $response->assertOk();
    }

    public function test_admin_foreign_kamar_denied_show(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kamar.show', $this->foreignKamar));
        $response->assertForbidden();
    }

    // ─── KAMAR CREATE ──────────────────────────────────────

    public function test_admin_assigned_can_create_kamar(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.kamar.store'), [
            'kos_id' => $this->assignedKos->id,
            'room_number' => 'A-99',
            'room_name' => 'Kamar Test Admin',
            'room_type' => 'Standard',
            'daily_price' => 100000,
            'monthly_price' => 1000000,
            'status' => 'available',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kamar', [
            'kos_id' => $this->assignedKos->id,
            'room_number' => 'A-99',
        ]);
    }

    public function test_admin_foreign_kos_cannot_create_kamar(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.kamar.store'), [
            'kos_id' => $this->foreignKos->id,
            'room_number' => 'B-99',
            'room_name' => 'Foreign Kamar',
            'room_type' => 'Standard',
            'daily_price' => 100000,
            'monthly_price' => 1000000,
            'status' => 'available',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('kamar', [
            'kos_id' => $this->foreignKos->id,
            'room_number' => 'B-99',
        ]);
    }

    // ─── KAMAR UPDATE ──────────────────────────────────────

    public function test_admin_assigned_can_update_kamar(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.kamar.update', $this->assignedKamar), [
            'room_number' => $this->assignedKamar->room_number,
            'room_name' => 'Updated by Admin',
            'room_type' => $this->assignedKamar->room_type,
            'daily_price' => $this->assignedKamar->daily_price,
            'monthly_price' => $this->assignedKamar->monthly_price,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kamar', [
            'id' => $this->assignedKamar->id,
            'room_name' => 'Updated by Admin',
        ]);
    }

    public function test_admin_foreign_kamar_denied_update(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.kamar.update', $this->foreignKamar), [
            'room_number' => $this->foreignKamar->room_number,
            'room_name' => 'Hacked',
            'room_type' => $this->foreignKamar->room_type,
            'daily_price' => $this->foreignKamar->daily_price,
            'monthly_price' => $this->foreignKamar->monthly_price,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('kamar', [
            'id' => $this->foreignKamar->id,
            'room_name' => 'Hacked',
        ]);
    }

    // ─── KAMAR DELETE ──────────────────────────────────────

public function test_admin_assigned_can_delete_eligible_kamar(): void
{
    $kamar = Kamar::factory()->create([
        'kos_id' => $this->assignedKos->id,
        'status' => 'available',
    ]);

    $response = $this->actingAs($this->admin)->delete(route('admin.kamar.destroy', $kamar));
    $response->assertStatus(302);
}

    public function test_admin_foreign_kamar_denied_delete(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.kamar.destroy', $this->foreignKamar));
        $response->assertForbidden();
        $this->assertDatabaseHas('kamar', ['id' => $this->foreignKamar->id]);
    }

    // ─── ADD PENGHUNI ──────────────────────────────────────

    public function test_admin_assigned_kamar_can_add_tenant(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);

        $response = $this->actingAs($this->admin)->post(route('admin.kamar.add-penghuni', $this->assignedKamar), [
            'user_id' => $tenant->id,
            'rental_type' => 'monthly',
            'rental_price' => 1000000,
            'check_in_date' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('penghunis', [
            'user_id' => $tenant->id,
            'kos_id' => $this->assignedKos->id,
            'kamar_id' => $this->assignedKamar->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_foreign_kamar_cannot_add_tenant(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);

        $response = $this->actingAs($this->admin)->post(route('admin.kamar.add-penghuni', $this->foreignKamar), [
            'user_id' => $tenant->id,
            'rental_type' => 'monthly',
            'rental_price' => 1000000,
            'check_in_date' => now()->toDateString(),
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('penghunis', [
            'user_id' => $tenant->id,
            'kamar_id' => $this->foreignKamar->id,
        ]);
    }

    // ─── TAMPERING ─────────────────────────────────────────

    public function test_admin_cannot_store_kamar_with_foreign_kos_id(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.kamar.store'), [
            'kos_id' => $this->foreignKos->id,
            'room_number' => 'T-001',
            'room_name' => 'Tampered',
            'room_type' => 'Standard',
            'daily_price' => 100000,
            'monthly_price' => 1000000,
            'status' => 'available',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_cannot_change_owner_id(): void
    {
        $response = $this->actingAs($this->admin)->patch(route('owner.kos.update', $this->assignedKos), [
            'name' => $this->assignedKos->name,
            'address' => $this->assignedKos->address,
            'phone' => $this->assignedKos->phone,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('kos', [
            'id' => $this->assignedKos->id,
            'owner_id' => $this->owner->id,
        ]);
    }

    public function test_admin_cannot_use_owner_mutation_route_for_kos(): void
    {
        $response = $this->actingAs($this->admin)->post(route('owner.kos.store'), [
            'name' => 'Stolen Kos',
            'address' => 'Jl. Hack',
            'phone' => '081234567890',
            'status' => 'active',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_cannot_use_owner_mutation_route_for_kamar(): void
    {
        $response = $this->actingAs($this->admin)->post(route('owner.kamar.store'), [
            'kos_id' => $this->assignedKos->id,
            'room_number' => 'X-001',
            'room_name' => 'Via Owner Route',
            'room_type' => 'Standard',
            'daily_price' => 100000,
            'monthly_price' => 1000000,
            'status' => 'available',
        ]);

        $response->assertStatus(403);
    }

    // ─── KOS CREATE/UPDATE REDIRECT ────────────────────

    public function test_admin_can_create_kos_and_redirect_to_admin_index(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($this->admin)->post(route('admin.kos.store'), [
            'name' => 'Kos Admin',
            'address' => 'Jl. Admin Test',
            'phone' => '081234567890',
            'status' => 'active',
            'owner_id' => $owner->id,
        ]);

        $response->assertRedirect(route('admin.kos.index'));
        $this->assertDatabaseHas('kos', [
            'name' => 'Kos Admin',
            'owner_id' => $owner->id,
        ]);
    }

    public function test_admin_can_update_kos_and_redirect_to_admin_index(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $this->admin->assignedKos()->attach($kos->id);

        $response = $this->actingAs($this->admin)->put(route('admin.kos.update', $kos), [
            'name' => 'Kos Updated by Admin',
            'address' => 'Jl. Updated',
            'phone' => '081234567890',
            'status' => 'active',
            'owner_id' => $owner->id,
        ]);

        $response->assertRedirect(route('admin.kos.index'));
        $this->assertDatabaseHas('kos', ['id' => $kos->id, 'name' => 'Kos Updated by Admin']);
    }

    public function test_owner_create_kos_redirects_to_owner_index(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name' => 'Kos Owner',
            'address' => 'Jl. Owner Test',
            'phone' => '081234567890',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('owner.kos.index'));
    }

    public function test_owner_update_kos_redirects_to_owner_index(): void
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id]);

        $response = $this->actingAs($this->owner)->put(route('owner.kos.update', $kos), [
            'name' => 'Kos Updated',
            'address' => 'Jl. Updated',
            'phone' => '081234567890',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('owner.kos.index'));
    }

    // ─── ROLE BOUNDARIES ───────────────────────────────────

    public function test_super_admin_retains_global_access(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($superAdmin)->get(route('admin.kos.index'));
        $response->assertOk();

        $response = $this->actingAs($superAdmin)->get(route('admin.kos.show', $this->foreignKos));
        $response->assertOk();

        $response = $this->actingAs($superAdmin)->get(route('admin.kamar.show', $this->foreignKamar));
        $response->assertOk();
    }

    public function test_owner_retains_own_kos_access(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.kos.index'));
        $response->assertOk();
        $response->assertSee($this->assignedKos->name);
        $response->assertSee($this->foreignKos->name);
    }

    public function test_owner_foreign_kos_denied_on_admin_route(): void
    {
        $otherOwner = User::factory()->create(['role' => 'owner']);
        $otherKos = Kos::factory()->create(['owner_id' => $otherOwner->id]);

        $response = $this->actingAs($this->owner)->get(route('admin.kos.show', $otherKos));
        $response->assertForbidden();
    }

    public function test_tenant_denied_admin_mutation(): void
    {
        $response = $this->actingAs($this->tenant)->post(route('admin.kamar.store'), [
            'kos_id' => $this->assignedKos->id,
            'room_number' => 'T-999',
            'room_name' => 'Tenant Hack',
            'room_type' => 'Standard',
            'daily_price' => 100000,
            'monthly_price' => 1000000,
            'status' => 'available',
        ]);

        $response->assertStatus(403);
    }
}
