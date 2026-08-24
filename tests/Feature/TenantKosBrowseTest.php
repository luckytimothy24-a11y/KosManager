<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantKosBrowseTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_view_active_kos_list(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kosActive = Kos::factory()->create(['name' => 'KOSAKTIF', 'status' => 'active']);
        Kos::factory()->create(['name' => 'KOSNONAKTIF', 'status' => 'inactive']);

        $response = $this->actingAs($tenant)->get('/tenant/kos');

        $response->assertOk();
        $response->assertSee('KOSAKTIF');
        $response->assertDontSee('KOSNONAKTIF');
    }

    public function test_tenant_can_search_kos_by_name(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        Kos::factory()->create(['name' => 'KOSTARGET', 'status' => 'active']);
        Kos::factory()->create(['name' => 'KOSLAIN', 'status' => 'active']);

        $response = $this->actingAs($tenant)->get('/tenant/kos?q=TARGET');

        $response->assertOk();
        $response->assertSee('KOSTARGET');
        $response->assertDontSee('KOSLAIN');
    }

    public function test_tenant_can_view_kos_detail_with_available_rooms(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create([
            'status' => 'active',
            'name' => 'Kos Uji Detail',
            'address' => 'Jl. Uji No. 1',
            'phone' => '081000000000',
        ]);
        Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => '101', 'status' => 'available']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'room_number' => '102', 'status' => 'occupied']);

        $response = $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}");

        $response->assertOk();
        $response->assertSee('101');
        $response->assertDontSee('102');
    }

    public function test_tenant_cannot_view_inactive_kos_detail(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['status' => 'inactive']);

        $this->actingAs($tenant)->get("/tenant/kos/{$kos->id}")->assertNotFound();
    }

    public function test_owner_cannot_access_tenant_kos_browse(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)->get('/tenant/kos')->assertForbidden();
    }
}
