<?php

namespace Tests\Feature\Characterization;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FASE 0 — KARAKTERISASI AKSES ROLE.
 *
 * Test ini mengunci perilaku akses yang berlaku pada commit 3c03dd2.
 * Jangan "memperbaiki" ekspektasi di sini saat menambah role technician
 * atau mengubah RBAC; test ini sengaja menjadi baseline lama.
 */
class RoleAccessCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $owner;

    private User $admin;

    private User $tenant;

    private Kos $kos;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $this->owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->tenant = User::factory()->create(['role' => 'pencari', 'is_active' => true]);

        $this->kos = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'name' => 'Kos Karakterisasi Role',
            'status' => 'active',
        ]);
        $this->admin->assignedKos()->attach($this->kos->id);

        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'room_number' => 'R-001',
            'status' => 'available',
        ]);
    }

    /**
     * Matriks GET per role. Nilai 200/403 adalah perilaku saat ini,
     * termasuk perilaku super_admin yang boleh masuk area owner/admin.
     * // karakterisasi, bukan perilaku yang diinginkan.
     */
    public function test_get_route_matrix_for_current_roles(): void
    {
        $cases = [
            ['route' => 'super-admin.users.index', 'allowed' => ['super_admin']],
            ['route' => 'super-admin.owners.index', 'allowed' => ['super_admin']],
            ['route' => 'super-admin.admins.index', 'allowed' => ['super_admin']],
            ['route' => 'admin.kos.index', 'allowed' => ['super_admin', 'admin']],
            ['route' => 'admin.pembayaran.index', 'allowed' => ['super_admin', 'admin']],
            ['route' => 'owner.kos.index', 'allowed' => ['super_admin', 'owner']],
            ['route' => 'owner.pembayaran.index', 'allowed' => ['super_admin', 'owner']],
            ['route' => 'owner.laporan.index', 'allowed' => ['super_admin', 'owner']],
            ['route' => 'tenant.tagihan.index', 'allowed' => ['pencari']],
            ['route' => 'tenant.damage-reports.index', 'allowed' => ['pencari']],
            ['route' => 'profile.edit', 'allowed' => ['super_admin', 'owner', 'admin', 'pencari']],
            ['route' => 'notifications.index', 'allowed' => ['super_admin', 'owner', 'admin', 'pencari']],
        ];

        $users = [
            'super_admin' => $this->superAdmin,
            'owner' => $this->owner,
            'admin' => $this->admin,
            'pencari' => $this->tenant,
        ];

        foreach ($cases as $case) {
            foreach ($users as $role => $user) {
                $response = $this->actingAs($user)->get(route($case['route']));

                if (in_array($role, $case['allowed'], true)) {
                    $response->assertOk();
                } else {
                    $response->assertForbidden();
                }
            }
        }
    }

    public function test_guest_is_redirected_to_login_for_protected_routes(): void
    {
        foreach (['super-admin.users.index', 'admin.kos.index', 'owner.kos.index', 'tenant.tagihan.index'] as $route) {
            $this->get(route($route))->assertRedirect('/login');
        }
    }

    public function test_tenant_cannot_verify_payment_via_owner_endpoint(): void
    {
        $pembayaran = Pembayaran::factory()->create(['verification_status' => 'pending']);

        $this->actingAs($this->tenant)
            ->post(route('owner.pembayaran.verify', $pembayaran))
            ->assertForbidden();
    }

    public function test_owner_cannot_write_to_admin_or_super_admin_endpoints(): void
    {
        $this->actingAs($this->owner)
            ->post(route('admin.kos.store'), [])
            ->assertForbidden();

        $this->actingAs($this->owner)
            ->post(route('super-admin.users.store'), [])
            ->assertForbidden();
    }

    public function test_admin_cannot_write_to_super_admin_endpoints(): void
    {
        $this->actingAs($this->admin)
            ->post(route('super-admin.users.store'), [])
            ->assertForbidden();
    }

    public function test_super_admin_is_not_allowed_into_tenant_area(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('tenant.tagihan.index'))
            ->assertForbidden();

        $this->actingAs($this->superAdmin)
            ->get(route('tenant.damage-reports.index'))
            ->assertForbidden();
    }

    public function test_admin_scope_is_limited_to_assigned_kos(): void
    {
        $otherOwner = User::factory()->create(['role' => 'owner']);
        $foreignKos = Kos::factory()->create(['owner_id' => $otherOwner->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.kos.show', $foreignKos))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->get(route('admin.kos.show', $this->kos))
            ->assertOk();
    }

    public function test_owner_cannot_access_another_owners_kos(): void
    {
        $otherOwner = User::factory()->create(['role' => 'owner']);
        $foreignKos = Kos::factory()->create(['owner_id' => $otherOwner->id]);

        $this->actingAs($this->owner)
            ->get(route('owner.kos.show', $foreignKos))
            ->assertForbidden();

        $this->actingAs($this->owner)
            ->get(route('owner.kos.show', $this->kos))
            ->assertOk();
    }
}
