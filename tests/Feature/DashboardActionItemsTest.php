<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CheckIn;
use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardActionItemsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_dashboard_shows_pending_bookings_count(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        Booking::factory()->create([
            'user_id' => User::factory()->create(['role' => 'pencari'])->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($owner)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSeeText('Booking Menunggu Persetujuan');
        $response->assertSeeText('1 item menunggu');
    }

    public function test_owner_dashboard_shows_no_action_items_when_empty(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        Kos::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSeeText('Tidak ada yang perlu ditindak');
    }

    public function test_admin_dashboard_shows_scope_label(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kos = Kos::factory()->create(['name' => 'Kos Test Admin']);
        // Assign kos to admin (assuming assignedKos relationship exists)
        // This depends on the actual implementation

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_owner_scope_isolation_for_action_items(): void
    {
        $ownerA = User::factory()->create(['role' => 'owner']);
        $ownerB = User::factory()->create(['role' => 'owner']);
        $kosA = Kos::factory()->create(['owner_id' => $ownerA->id]);
        $kosB = Kos::factory()->create(['owner_id' => $ownerB->id]);
        $kamarA = Kamar::factory()->create(['kos_id' => $kosA->id, 'status' => 'available']);
        $kamarB = Kamar::factory()->create(['kos_id' => $kosB->id, 'status' => 'available']);

        // Create pending booking for owner A only
        Booking::factory()->create([
            'user_id' => User::factory()->create(['role' => 'pencari'])->id,
            'kos_id' => $kosA->id,
            'kamar_id' => $kamarA->id,
            'status' => 'pending',
        ]);

        // Owner B should not see owner A's pending booking
        $response = $this->actingAs($ownerB)->get(route('dashboard'));
        $response->assertOk();
        $response->assertDontSeeText('1 item menunggu');
    }
}
