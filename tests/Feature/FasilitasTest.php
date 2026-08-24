<?php

namespace Tests\Feature;

use App\Models\Fasilitas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FasilitasTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
    }

    public function test_super_admin_can_view_fasilitas_list(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('super-admin.fasilitas.index'));
        $response->assertStatus(200);
    }

    public function test_owner_cannot_view_fasilitas(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $response = $this->actingAs($owner)->get(route('super-admin.fasilitas.index'));
        $response->assertStatus(403);
    }

    public function test_super_admin_can_create_fasilitas(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('super-admin.fasilitas.store'), [
            'name' => 'WiFi',
            'icon' => 'wifi',
        ]);

        $response->assertRedirect(route('super-admin.fasilitas.index'));
        $this->assertDatabaseHas('fasilitas', ['name' => 'WiFi', 'icon' => 'wifi']);
    }

    public function test_super_admin_can_update_fasilitas(): void
    {
        $fasilitas = Fasilitas::factory()->create(['name' => 'AC']);

        $response = $this->actingAs($this->superAdmin)->put(route('super-admin.fasilitas.update', $fasilitas), [
            'name' => 'AC Split',
        ]);

        $this->assertDatabaseHas('fasilitas', ['id' => $fasilitas->id, 'name' => 'AC Split']);
    }

    public function test_super_admin_can_delete_fasilitas(): void
    {
        $fasilitas = Fasilitas::factory()->create();

        $response = $this->actingAs($this->superAdmin)->delete(route('super-admin.fasilitas.destroy', $fasilitas));
        $response->assertRedirect(route('super-admin.fasilitas.index'));
        $this->assertDatabaseMissing('fasilitas', ['id' => $fasilitas->id]);
    }
}
