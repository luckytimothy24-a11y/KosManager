<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_cannot_approve_checkout_of_another_owners_kos(): void
    {
        $ownerA = User::factory()->create(['role' => 'owner']);
        $ownerB = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $ownerA->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'occupied']);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $checkOut = CheckOut::create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'request_date' => now(),
            'status' => 'pending',
        ]);

        $this->actingAs($ownerB)->post("/owner/check-out/{$checkOut->id}/approve")->assertForbidden();

        $this->assertDatabaseHas('check_outs', ['id' => $checkOut->id, 'status' => 'pending']);
        $this->assertDatabaseHas('penghunis', ['id' => $penghuni->id, 'status' => 'active']);
    }

    public function test_owner_cannot_create_tagihan_for_another_owners_kontrak(): void
    {
        $ownerA = User::factory()->create(['role' => 'owner']);
        $ownerB = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $ownerA->id]);
        $kontrak = Kontrak::factory()->create(['kos_id' => $kos->id, 'status' => 'active']);

        $response = $this->actingAs($ownerB)->post('/owner/tagihan', [
            'kontrak_id' => $kontrak->id,
            'bill_type' => 'rent',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'subtotal' => 1000000,
            'due_date' => now()->addDays(10)->toDateString(),
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('tagihans', 0);
    }

    public function test_owner_cannot_create_tagihan_for_inactive_kontrak(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kontrak = Kontrak::factory()->create(['kos_id' => $kos->id, 'status' => 'expired']);

        $response = $this->actingAs($owner)->post('/owner/tagihan', [
            'kontrak_id' => $kontrak->id,
            'bill_type' => 'rent',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'subtotal' => 1000000,
            'due_date' => now()->addDays(10)->toDateString(),
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseCount('tagihans', 0);
    }

    public function test_tenant_cannot_submit_payment_for_paid_tagihan(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create();
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($tenant)->post('/tenant/pembayaran', [
            'tagihan_id' => $tagihan->id,
            'amount' => 1000000,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('pembayarans', 0);
        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id, 'status' => 'paid']);
    }

    public function test_booking_rejects_range_contained_in_existing_booking(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(30),
            'status' => 'approved',
        ]);

        $response = $this->actingAs($tenant)->post('/tenant/booking', [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'rental_type' => 'daily',
        ]);

        $response->assertSessionHasErrors('kamar_id');
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_booking_rejects_mismatched_kos_and_kamar(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kosA = Kos::factory()->create(['status' => 'active']);
        $kosB = Kos::factory()->create(['status' => 'active']);
        $kamarB = Kamar::factory()->create(['kos_id' => $kosB->id]);

        $response = $this->actingAs($tenant)->post('/tenant/booking', [
            'kos_id' => $kosA->id,
            'kamar_id' => $kamarB->id,
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'rental_type' => 'daily',
        ]);

        $response->assertSessionHasErrors('kamar_id');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_cancelling_approved_booking_frees_the_kamar(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create();
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'booked']);
        $booking = Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
        ]);

        $this->actingAs($tenant)->post("/tenant/booking/{$booking->id}/cancel")->assertRedirect();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'available']);
    }

    public function test_rejected_payment_resets_tagihan_and_cannot_be_verified_afterwards(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $kamar->id,
            'status' => 'pending_verification',
        ]);
        $pembayaran = Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'penghuni_id' => $penghuni->id,
            'verification_status' => 'pending',
        ]);

        $this->actingAs($owner)->post("/owner/pembayaran/{$pembayaran->id}/reject")->assertRedirect();

        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id, 'status' => 'unpaid']);

        $this->actingAs($owner)->post("/owner/pembayaran/{$pembayaran->id}/verify")->assertStatus(400);

        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id, 'status' => 'unpaid']);
    }

    public function test_admin_without_assignment_cannot_view_other_kos_booking(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create();
        $booking = Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
        ]);

        $this->actingAs($admin)->get("/owner/booking/{$booking->id}")->assertForbidden();
    }

    public function test_super_admin_cannot_delete_own_account_or_last_super_admin(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)->delete("/super-admin/users/{$superAdmin->id}")->assertStatus(400);
        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('password'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }
}
