<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KamarPageTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Kos $kos;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id]);
    }

    public function test_admin_can_view_kamar_index_without_error(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->kos->admins()->syncWithoutDetaching([$admin->id]);

        $response = $this->actingAs($admin)->get(route('admin.kamar.index'));

        $response->assertStatus(200);
        $response->assertSee($this->kamar->room_number);
        $response->assertDontSee('Edit</a>', false);
        $response->assertDontSee('Hapus', false);
        $response->assertDontSee('Tambah Kamar');
    }

    public function test_owner_sees_manage_actions_on_kamar_index(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.kamar.index'));

        $response->assertStatus(200);
        $response->assertSee('Tambah Kamar');
        $response->assertSee(route('owner.kamar.edit', $this->kamar));
        $response->assertSee(route('owner.kamar.destroy', $this->kamar));
    }

    public function test_admin_can_view_kamar_detail(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->kos->admins()->syncWithoutDetaching([$admin->id]);

        $response = $this->actingAs($admin)->get(route('admin.kamar.show', $this->kamar));

        $response->assertStatus(200);
        $response->assertSee($this->kamar->room_name);
    }
}
