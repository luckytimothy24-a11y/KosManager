<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tenant_without_overdue_invoices_can_access_tenant_routes()
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);

        // Create kos and kamar
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);

        // Create penghuni (no tagihan created yet)
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        // No overdue invoices - should pass billing middleware
        $this->actingAs($tenant)->get('/tenant/kos')->assertOk();
    }

    /** @test */
    public function tenant_with_overdue_invoices_gets_blocked_by_billing_middleware()
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);

        // Create kos and kamar
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);

        // Create penghuni
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        // Create overdue tagihan
        Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'status' => Tagihan::STATUS_OVERDUE,
            'total' => 1000000,
            'subtotal' => 1000000,
        ]);

        // Tenant with overdue invoices should get 403 or error message
        $response = $this->actingAs($tenant)->get('/tenant/kos');
        // Should either be redirected with error or show 403
        $response->assertStatus(302) // redirect
            ->assertSessionHas('error', 'Anda memiliki tagihan yang belum dibayar');
    }

    /** @test */
    public function tenant_can_access_dashboard_without_billing_block()
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($tenant)->get('/dashboard')->assertOk();
    }

    /** @test */
    public function tenant_can_access_tagihan_page_without_overdue()
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);

        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);

        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        // Tagihan that is NOT overdue (unpaid but not overdue status)
        Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'status' => 'unpaid', // not overdue
            'total' => 1000000,
            'subtotal' => 1000000,
        ]);

        // Should be able to access tagihan page
        $response = $this->actingAs($tenant)->get('/tenant/tagihan');
        $response->assertOk();
    }
}
