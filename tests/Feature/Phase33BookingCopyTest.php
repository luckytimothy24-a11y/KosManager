<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Copy halaman booking mengikuti kebijakan satu kamar = satu penghuni.
 * Patungan/multi-occupant sudah dihapus dari produk.
 */
class Phase33BookingCopyTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_form_shows_single_occupant_copy(): void
    {
        $tenant = User::factory()->create(['role' => 'pencari']);
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'capacity' => 1, 'billing_mode' => 'personal', 'monthly_price' => 1000000]);

        $res = $this->actingAs($tenant)->get(route('tenant.booking.create', ['kos_id' => $kos->id]));

        $res->assertOk();
        $res->assertSee('Jumlah Penghuni');
        $res->assertSee('satu penghuni');
        $res->assertSee('Patungan tidak lagi didukung.');
        $res->assertSee('Pembayaran');
        $res->assertSee('Biaya sewa');
        $res->assertSee('DP dibayar saat booking');
        // Nilai backend tetap dikirim sebagai hidden input.
        $this->assertStringContainsString('name="requested_occupants"', $res->getContent());
        $this->assertStringContainsString('name="billing_mode"', $res->getContent());
        $this->assertStringContainsString('value="personal"', $res->getContent());
    }

    public function test_booking_show_displays_single_occupant_copy(): void
    {
        $tenant = User::factory()->create(['role' => 'pencari']);
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'capacity' => 1, 'billing_mode' => 'personal', 'monthly_price' => 1000000]);
        $booking = Booking::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'approved',
            'requested_occupants' => 1,
            'billing_mode' => 'personal',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addMonths(1),
            'rental_type' => 'monthly',
            'price' => 1000000,
        ]);

        $res = $this->actingAs($tenant)->get(route('tenant.booking.show', $booking));

        $res->assertOk();
        $res->assertSee('Penghuni');
        $res->assertSee('1 orang (satu kamar untuk satu penghuni)');
        $res->assertSee('Pembayaran');
        $res->assertSee('Tagihan pribadi');
        $res->assertSee('Harga sewa');
    }

    public function test_personal_booking_still_works(): void
    {
        $tenant = User::factory()->create(['role' => 'pencari']);
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'capacity' => 1, 'billing_mode' => 'personal', 'monthly_price' => 1000000, 'daily_price' => 50000, 'weekly_price' => 300000]);
        $res = $this->actingAs($tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $kos->id, 'kamar_id' => $kamar->id, 'start_date' => now()->addDays(1)->format('Y-m-d'), 'end_date' => now()->addMonths(1)->format('Y-m-d'), 'rental_type' => 'monthly', 'billing_mode' => 'personal', 'requested_occupants' => 1,
            'identity_number' => '3201234567890001', 'phone' => '08123456789', 'address' => 'Jl Test No 1',
        ]);
        $res->assertRedirect();
        $this->assertDatabaseHas('bookings', ['user_id' => $tenant->id, 'kamar_id' => $kamar->id, 'billing_mode' => 'personal', 'requested_occupants' => 1]);
    }
}
