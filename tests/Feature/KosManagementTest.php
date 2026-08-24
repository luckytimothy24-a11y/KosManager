<?php

namespace Tests\Feature;

use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KosManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
    }

    public function test_owner_can_view_kos_list(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.kos.index'));
        $response->assertStatus(200);
    }

    public function test_tenant_cannot_view_kos_list(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('owner.kos.index'));
        $response->assertStatus(403);
    }

    public function test_owner_can_create_kos(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kos.store'), [
            'name' => 'Kos Baru',
            'address' => 'Jl. Test No. 1',
            'phone' => '08123456789',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('owner.kos.index'));
        $this->assertDatabaseHas('kos', [
            'name' => 'Kos Baru',
            'owner_id' => $this->owner->id,
        ]);
    }

    public function test_owner_can_update_own_kos(): void
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id]);

        $response = $this->actingAs($this->owner)->put(route('owner.kos.update', $kos), [
            'name' => 'Kos Updated',
            'address' => 'Jl. Updated',
            'phone' => '08999999999',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('owner.kos.index'));
        $this->assertDatabaseHas('kos', ['id' => $kos->id, 'name' => 'Kos Updated']);
    }

    public function test_owner_cannot_update_other_owner_kos(): void
    {
        $otherOwner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $otherOwner->id]);

        $response = $this->actingAs($this->owner)->put(route('owner.kos.update', $kos), [
            'name' => 'Hacked',
            'address' => 'Jl. Hack',
            'phone' => '08111111111',
            'status' => 'active',
        ]);

        $response->assertStatus(403);
    }

    public function test_owner_can_delete_empty_kos(): void
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id]);

        $response = $this->actingAs($this->owner)->delete(route('owner.kos.destroy', $kos));
        $response->assertRedirect(route('owner.kos.index'));
        $this->assertSoftDeleted('kos', ['id' => $kos->id]);
    }

    public function test_owner_can_view_kos_detail(): void
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id]);

        $response = $this->actingAs($this->owner)->get(route('owner.kos.show', $kos));
        $response->assertStatus(200);
    }

    public function test_super_admin_can_view_kos_list_and_detail(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id]);

        $this->actingAs($superAdmin)->get(route('owner.kos.index'))
            ->assertStatus(200)
            ->assertSee(route('owner.kos.show', $kos), false);

        $this->actingAs($superAdmin)->get(route('owner.kos.show', $kos))
            ->assertStatus(200);
    }

    public function test_admin_cannot_view_kos_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('owner.kos.index'))
            ->assertStatus(403);
    }
}
