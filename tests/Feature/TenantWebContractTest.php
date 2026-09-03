<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantWebContractTest extends TestCase
{
    use RefreshDatabase;

    private User $tenant;

    private User $otherTenant;

    private User $owner;

    private Kos $kos;

    private Kamar $kamar;

    private Penghuni $penghuni;

    private Kontrak $kontrak;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('secret123')]);
        $this->otherTenant = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('secret123')]);

        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'occupied']);

        $this->penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);

        $this->kontrak = Kontrak::factory()->create([
            'penghuni_id' => $this->penghuni->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'rental_type' => 'monthly',
            'rental_price' => 1500000,
            'start_date' => now()->subMonths(2),
            'end_date' => now()->addMonths(4),
            'status' => 'active',
            'notes' => 'Kontrak bulanan',
        ]);
    }

    private function makeOtherKontrak(): Kontrak
    {
        $kosB = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
        $kamarB = Kamar::factory()->create(['kos_id' => $kosB->id, 'status' => 'occupied']);
        $penghuniB = Penghuni::factory()->create([
            'user_id' => $this->otherTenant->id,
            'kos_id' => $kosB->id,
            'kamar_id' => $kamarB->id,
            'status' => 'active',
        ]);

        return Kontrak::factory()->create([
            'penghuni_id' => $penghuniB->id,
            'kos_id' => $kosB->id,
            'kamar_id' => $kamarB->id,
            'rental_type' => 'daily',
            'rental_price' => 100000,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
            'status' => 'active',
        ]);
    }

    // ------------------------------------------------------------- index

    public function test_tenant_can_view_own_contract_list(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.kontrak.index'));

        $response->assertOk();
        $response->assertViewHas('kontraks');
        $response->assertSee(e($this->kontrak->contract_number));
        $response->assertSee($this->kos->name);
    }

    public function test_tenant_contract_list_is_scoped_to_own_contracts(): void
    {
        $other = $this->makeOtherKontrak();

        $response = $this->actingAs($this->tenant)->get(route('tenant.kontrak.index'));
        $response->assertOk();

        $viewKontraks = $response->viewData('kontraks');
        $ids = $viewKontraks->getCollection()->pluck('id')->all();
        $this->assertContains($this->kontrak->id, $ids);
        $this->assertNotContains($other->id, $ids);

        // Must not render the other tenant's contract number.
        $response->assertDontSee(e($other->contract_number));
    }

    public function test_tenant_with_no_contracts_sees_empty_state(): void
    {
        $fresh = User::factory()->create(['role' => 'tenant', 'password' => Hash::make('x')]);

        $response = $this->actingAs($fresh)->get(route('tenant.kontrak.index'));

        $response->assertOk();
        $response->assertSee('Belum ada kontrak');
    }

    // ------------------------------------------------------------- show

    public function test_tenant_can_view_own_contract_detail(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('tenant.kontrak.show', $this->kontrak));

        $response->assertOk();
        $response->assertViewHas('kontrak');
        $response->assertSee(e($this->kontrak->contract_number));
        $response->assertSee($this->kos->name);
        $response->assertSee($this->kamar->room_number);
        $response->assertSee('Rp 1.500.000');
    }

    public function test_tenant_cannot_view_another_tenants_contract(): void
    {
        $other = $this->makeOtherKontrak();

        $this->actingAs($this->tenant)->get(route('tenant.kontrak.show', $other))
            ->assertStatus(403);
    }

    public function test_tenant_contract_detail_does_not_leak_other_tenants_billing(): void
    {
        $other = $this->makeOtherKontrak();
        Tagihan::factory()->create([
            'penghuni_id' => $other->penghuni_id,
            'kontrak_id' => $other->id,
            'kamar_id' => $other->kamar_id,
            'status' => 'unpaid',
            'total' => 999000,
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.kontrak.show', $this->kontrak));
        $response->assertOk();
        $response->assertDontSee('999000');
    }

    public function test_tenant_contract_detail_shows_related_billing_navigation(): void
    {
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $this->kontrak->penghuni_id,
            'kontrak_id' => $this->kontrak->id,
            'kamar_id' => $this->kontrak->kamar_id,
            'status' => 'unpaid',
            'total' => 1500000,
            'due_date' => now()->addDays(7),
        ]);

        $response = $this->actingAs($this->tenant)->get(route('tenant.kontrak.show', $this->kontrak));
        $response->assertOk();
        $response->assertSee(e($tagihan->bill_number));
        $response->assertSee(route('tenant.tagihan.show', $tagihan));
    }

    // ------------------------------------------------------------- roles

    public function test_unauthenticated_users_redirected_from_tenant_contract_list(): void
    {
        $this->get(route('tenant.kontrak.index'))->assertRedirect(route('login'));
    }

    public function test_unauthenticated_users_redirected_from_tenant_contract_detail(): void
    {
        $this->get(route('tenant.kontrak.show', $this->kontrak))->assertRedirect(route('login'));
    }

    public function test_owner_cannot_access_tenant_contract_routes(): void
    {
        $this->actingAs($this->owner)->get(route('tenant.kontrak.index'))->assertStatus(403);
        $this->actingAs($this->owner)->get(route('tenant.kontrak.show', $this->kontrak))->assertStatus(403);
    }

    public function test_superadmin_retains_owner_contract_access(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        // Super admin can still see the tenant-facing list scope as a tenant route is 403,
        // but they retain access to the owner contract route (non-tenant authorization intact).
        $this->actingAs($superAdmin)->get(route('owner.kontrak.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('owner.kontrak.show', $this->kontrak))->assertOk();
    }
}
