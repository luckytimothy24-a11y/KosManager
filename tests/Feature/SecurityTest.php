<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_cannot_access_owner_routes(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);

        $routes = [
            route('owner.kos.index'),
            route('owner.kamar.index'),
            route('owner.booking.index'),
            route('owner.penghuni.index'),
            route('owner.kontrak.index'),
            route('owner.tagihan.index'),
            route('owner.pembayaran.index'),
            route('owner.checkin.index'),
            route('owner.checkout.index'),
        ];

        foreach ($routes as $url) {
            $response = $this->actingAs($tenant)->get($url);
            $response->assertStatus(403);
        }
    }

    public function test_owner_cannot_access_super_admin_routes(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $routes = [
            route('super-admin.users.index'),
            route('super-admin.fasilitas.index'),
            route('super-admin.laporan.index'),
            route('super-admin.audit-log.index'),
        ];

        foreach ($routes as $url) {
            $response = $this->actingAs($owner)->get($url);
            $response->assertStatus(403);
        }
    }

    public function test_owner_cannot_update_other_owner_kos(): void
    {
        $owner1 = User::factory()->create(['role' => 'owner']);
        $owner2 = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner2->id]);

        $response = $this->actingAs($owner1)->put(route('owner.kos.update', $kos), [
            'name' => 'Hacked',
            'address' => 'Jl. Hack',
            'phone' => '08111111111',
            'status' => 'active',
        ]);
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_role_middleware_blocks_wrong_role(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $response = $this->actingAs($tenant)->get(route('owner.kos.index'));
        $response->assertStatus(403);
    }

    public function test_owner_cannot_view_other_owner_bookings(): void
    {
        $owner1 = User::factory()->create(['role' => 'owner']);
        $owner2 = User::factory()->create(['role' => 'owner']);
        $kos2 = Kos::factory()->create(['owner_id' => $owner2->id]);
        $kamar2 = Kamar::factory()->create(['kos_id' => $kos2->id]);
        $booking = Booking::factory()->create([
            'kos_id' => $kos2->id,
            'kamar_id' => $kamar2->id,
        ]);

        $response = $this->actingAs($owner1)->get(route('owner.booking.show', $booking));
        $response->assertStatus(403);
    }
}
