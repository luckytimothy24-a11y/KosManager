<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Security regression: status booking/tenant tidak boleh dimanipulasi dari frontend.
 *
 * Rules:
 * - Tenant membuat booking -> pending
 * - Tenant tidak bisa mengubah pending -> confirmed/approved/completed
 * - Tenant tidak bisa mengubah cancelled/rejected -> confirmed
 * - Booking dibuat -> tenant TIDAK otomatis active
 * - Request dengan status=confirmed / tenant_status=active diabaikan backend
 */
class BookingStatusSeparationSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): User
    {
        return User::factory()->create(['role' => 'tenant']);
    }

    private function owner(): User
    {
        return User::factory()->create(['role' => 'owner']);
    }

    private function kosAndRoom(User $owner): array
    {
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create([
            'kos_id' => $kos->id,
            'status' => 'available',
            'monthly_price' => 1500000,
            'daily_price' => 100000,
            'weekly_price' => 400000,
        ]);

        return [$kos, $kamar];
    }

    public function test_tenant_booking_store_ignores_manipulative_status_fields(): void
    {
        $tenant = $this->tenant();
        $owner = $this->owner();
        [$kos, $kamar] = $this->kosAndRoom($owner);

        $this->actingAs($tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'rental_type' => 'monthly',
            'identity_number' => '3201234567890001',
            'phone' => '081234567890',
            'address' => 'Jl. Manipulasi No. 1',
            // Manipulasi frontend — harus diabaikan
            'status' => 'approved',
            'tenant_status' => 'active',
            'payment_status' => 'approved',
        ])->assertRedirect();

        $booking = Booking::where('user_id', $tenant->id)->firstOrFail();

        $this->assertEquals('pending', $booking->status);
        $this->assertDatabaseMissing('penghunis', [
            'user_id' => $tenant->id,
            'status' => 'active',
        ]);
    }

    public function test_api_booking_store_ignores_manipulative_status_fields(): void
    {
        $tenant = $this->tenant();
        $owner = $this->owner();
        [$kos, $kamar] = $this->kosAndRoom($owner);

        $token = $tenant->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/bookings', [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'rental_type' => 'monthly',
            'status' => 'confirmed',
            'tenant_status' => 'active',
        ])->assertStatus(201);

        $booking = Booking::where('user_id', $tenant->id)->firstOrFail();

        $this->assertEquals('pending', $booking->status);
        $this->assertDatabaseMissing('penghunis', ['user_id' => $tenant->id, 'status' => 'active']);
    }

    public function test_tenant_cannot_approve_own_booking(): void
    {
        $tenant = $this->tenant();
        $owner = $this->owner();
        [$kos, $kamar] = $this->kosAndRoom($owner);

        $booking = Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'pending',
        ]);

        $this->actingAs($tenant)
            ->post(route('owner.booking.approve', $booking))
            ->assertForbidden();

        $this->assertEquals('pending', $booking->fresh()->status);
        $this->assertDatabaseMissing('penghunis', ['user_id' => $tenant->id, 'status' => 'active']);
    }

    public function test_owner_cannot_approve_already_rejected_or_cancelled_booking(): void
    {
        $tenant = $this->tenant();
        $owner = $this->owner();
        [$kos, $kamar] = $this->kosAndRoom($owner);

        $rejected = Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'rejected',
        ]);

        $this->actingAs($owner)
            ->post(route('owner.booking.approve', $rejected))
            ->assertForbidden();

        $this->assertEquals('rejected', $rejected->fresh()->status);

        $cancelled = Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'cancelled',
        ]);

        $this->actingAs($owner)
            ->post(route('owner.booking.approve', $cancelled))
            ->assertForbidden();

        $this->assertEquals('cancelled', $cancelled->fresh()->status);
    }

    public function test_payment_verification_does_not_activate_tenant_or_confirm_booking(): void
    {
        $tenant = $this->tenant();
        $owner = $this->owner();
        [$kos, $kamar] = $this->kosAndRoom($owner);

        $booking = Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'pending',
        ]);

        $penghuni = Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'inactive',
        ]);

        $kontrak = Kontrak::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $kamar->id,
            'status' => 'unpaid',
        ]);

        $pembayaran = Pembayaran::factory()->create([
            'penghuni_id' => $penghuni->id,
            'tagihan_id' => $tagihan->id,
            'verification_status' => 'pending',
            'amount' => $tagihan->total,
        ]);

        $this->actingAs($owner)
            ->post(route('owner.pembayaran.verify', $pembayaran))
            ->assertRedirect();

        $this->assertDatabaseHas('pembayarans', [
            'id' => $pembayaran->id,
            'verification_status' => 'approved',
        ]);
        // Payment approved ≠ booking confirmed
        $this->assertEquals('pending', $booking->fresh()->status);
        // Payment approved ≠ tenant active
        $this->assertEquals('inactive', $penghuni->fresh()->status);
    }

    public function test_model_guard_blocks_illegal_status_update(): void
    {
        $tenant = $this->tenant();
        $owner = $this->owner();
        [$kos, $kamar] = $this->kosAndRoom($owner);

        $booking = Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'pending',
        ]);

        $this->expectException(\RuntimeException::class);

        $booking->update(['status' => 'completed']);
    }

    public function test_owner_approve_then_tenant_active_only_after_checkin(): void
    {
        $tenant = $this->tenant();
        $owner = $this->owner();
        [$kos, $kamar] = $this->kosAndRoom($owner);

        $booking = Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'pending',
            'price' => 1000000,
        ]);

        // Payment prerequisite
        $pay = app(PaymentService::class)->createForBooking($booking, $tenant, 'cash');
        $this->assertTrue($pay['ok']);
        $this->actingAs($owner)->post(route('owner.pembayaran.verify', $pay['pembayaran']));

        $this->actingAs($owner)->post(route('owner.booking.approve', $booking));
        $this->assertEquals('approved', $booking->fresh()->status);
        $this->assertDatabaseMissing('penghunis', ['user_id' => $tenant->id, 'status' => 'active']);
    }

    public function test_owner_cannot_confirm_paid_booking_without_payment_is_blocked(): void
    {
        $tenant = $this->tenant();
        $owner = $this->owner();
        [$kos, $kamar] = $this->kosAndRoom($owner);

        $booking = Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'pending',
            'price' => 1000000,
        ]);

        $this->actingAs($owner)
            ->post(route('owner.booking.approve', $booking))
            ->assertSessionHas('error');

        $this->assertEquals('pending', $booking->fresh()->status);
    }
}
