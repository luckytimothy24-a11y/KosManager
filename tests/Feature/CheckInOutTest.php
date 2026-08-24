<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckInOutTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Kos $kos;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id]);
        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'booked']);
    }

    public function test_owner_can_process_checkin(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.checkin.process', $booking), [
            'identity_number' => '3201234567890001',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
            'notes' => 'Check-in lancar',
        ]);

        $response->assertRedirect(route('owner.booking.index'));
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'completed']);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'occupied']);
        $this->assertDatabaseHas('penghunis', [
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('kontraks', [
            'penghuni_id' => Penghuni::where('user_id', $this->tenant->id)->first()->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('check_ins', [
            'kamar_id' => $this->kamar->id,
            'officer_id' => $this->owner->id,
        ]);
    }

    public function test_cannot_checkin_unapproved_booking(): void
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->owner)->post(route('owner.checkin.process', $booking));
        $response->assertStatus(400);
    }

    public function test_assigned_admin_can_process_checkin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->kos->admins()->syncWithoutDetaching([$admin->id]);

        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.checkin.process', $booking), [
            'identity_number' => '3201234567890002',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
        ]);

        $response->assertRedirect(route('admin.booking.index'));
        $this->assertDatabaseHas('check_ins', [
            'kamar_id' => $this->kamar->id,
            'officer_id' => $admin->id,
        ]);
    }

    public function test_unassigned_admin_cannot_process_checkin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $booking = Booking::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'approved',
        ]);

        $this->actingAs($admin)->post(route('admin.checkin.process', $booking), [
            'identity_number' => '3201234567890003',
            'phone' => '08123456789',
            'address' => 'Jl. Test',
        ])->assertForbidden();
    }

    public function test_owner_can_process_checkout(): void
    {
        $penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);
        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);

        $checkOut = CheckOut::create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $this->kamar->id,
            'request_date' => now(),
            'status' => 'pending',
        ]);

        $this->kamar->update(['status' => 'occupied']);

        $response = $this->actingAs($this->owner)->post(route('owner.checkout.approve', $checkOut));
        $response->assertRedirect(route('owner.checkout.index'));

        $this->assertDatabaseHas('check_outs', ['id' => $checkOut->id, 'status' => 'approved']);
        $this->assertDatabaseHas('penghunis', ['id' => $penghuni->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'available']);
        // Kontrak berakhir jauh di masa depan -> check-out sekarang = terminated (PRD §6).
        $this->assertDatabaseHas('kontraks', ['id' => $kontrak->id, 'status' => 'terminated']);
    }

    public function test_tenant_can_request_checkout(): void
    {
        $penghuni = Penghuni::factory()->create([
            'user_id' => $this->tenant->id,
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->tenant)->post(route('tenant.checkout.request', $penghuni));
        $response->assertRedirect();
        $this->assertDatabaseHas('check_outs', [
            'penghuni_id' => $penghuni->id,
            'status' => 'pending',
        ]);
    }

    public function test_owner_can_view_checkin_list(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.checkin.index'));
        $response->assertStatus(200);
    }

    public function test_owner_can_view_checkout_list(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.checkout.index'));
        $response->assertStatus(200);
    }
}
