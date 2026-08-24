<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KamarManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Kos $kos;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'kos_id' => $this->kos->id,
            'room_number' => '101',
            'room_name' => 'Kamar Mawar',
            'room_type' => 'standard',
            'daily_price' => 50000,
            'monthly_price' => 1000000,
        ], $overrides);
    }

    public function test_owner_can_create_kamar(): void
    {
        $response = $this->actingAs($this->owner)->post(route('owner.kamar.store'), $this->validPayload());

        $response->assertRedirect(route('owner.kamar.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('kamar', [
            'kos_id' => $this->kos->id,
            'room_number' => '101',
            'status' => 'available',
        ]);
    }

    public function test_owner_can_update_kamar(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);

        $response = $this->actingAs($this->owner)->put(route('owner.kamar.update', $kamar), $this->validPayload([
            'room_number' => '202',
            'room_name' => 'Kamar Melati',
        ]));

        $response->assertRedirect(route('owner.kamar.index'));

        $this->assertDatabaseHas('kamar', [
            'id' => $kamar->id,
            'room_number' => '202',
            'room_name' => 'Kamar Melati',
        ]);
    }

    public function test_owner_cannot_delete_kamar_with_active_penghuni(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);
        Penghuni::factory()->create([
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->owner)->delete(route('owner.kamar.destroy', $kamar));

        $response->assertRedirect(route('owner.kamar.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('kamar', ['id' => $kamar->id]);
    }

    public function test_owner_cannot_delete_kamar_with_active_booking(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);
        $tenant = User::factory()->create(['role' => 'tenant']);

        Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->owner)->delete(route('owner.kamar.destroy', $kamar));

        $response->assertRedirect(route('owner.kamar.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('kamar', ['id' => $kamar->id]);
    }

    public function test_owner_can_delete_empty_kamar(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);

        $response = $this->actingAs($this->owner)->delete(route('owner.kamar.destroy', $kamar));

        $response->assertRedirect(route('owner.kamar.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('kamar', ['id' => $kamar->id]);
    }

    public function test_owner_cannot_manage_kamar_of_other_owner(): void
    {
        $otherOwner = User::factory()->create(['role' => 'owner']);
        $otherKos = Kos::factory()->create(['owner_id' => $otherOwner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $otherKos->id]);

        $store = $this->actingAs($this->owner)->post(route('owner.kamar.store'), $this->validPayload([
            'kos_id' => $otherKos->id,
        ]));
        $store->assertForbidden();

        $edit = $this->actingAs($this->owner)->get(route('owner.kamar.edit', $kamar));
        $edit->assertForbidden();

        $update = $this->actingAs($this->owner)->put(route('owner.kamar.update', $kamar), $this->validPayload());
        $update->assertForbidden();

        $delete = $this->actingAs($this->owner)->delete(route('owner.kamar.destroy', $kamar));
        $delete->assertForbidden();
    }
}
