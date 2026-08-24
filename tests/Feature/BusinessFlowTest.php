<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Notification;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BusinessFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_lifecycle_booking_to_checkout(): void
    {
        Mail::fake();

        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create([
            'kos_id' => $kos->id,
            'status' => 'available',
            'monthly_price' => 1500000,
            'daily_price' => 100000,
        ]);

        // 1. Tenant booking
        $this->actingAs($tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'rental_type' => 'monthly',
        ])->assertRedirect();
        $this->assertDatabaseHas('bookings', ['user_id' => $tenant->id, 'status' => 'pending']);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'available']);

        // 2. Owner approve -> kamar booked
        $booking = Booking::where('user_id', $tenant->id)->firstOrFail();
        $this->actingAs($owner)->post(route('owner.booking.approve', $booking))->assertRedirect();
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'approved']);
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'booked']);

        // 3. Check-in -> penghuni aktif + kontrak aktif + kamar occupied + booking completed
        $this->actingAs($owner)->post(route('owner.checkin.process', $booking), [
            'identity_number' => '3201234567890001',
            'phone' => '081234567890',
            'address' => 'Jl. Test No. 1',
        ])->assertRedirect();
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'occupied']);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'completed']);

        $penghuni = Penghuni::where('user_id', $tenant->id)->firstOrFail();
        $this->assertEquals('active', $penghuni->status);
        $kontrak = Kontrak::where('penghuni_id', $penghuni->id)->firstOrFail();
        $this->assertEquals('active', $kontrak->status);

        // 4. Tagihan dibuat dari kontrak -> tenant bayar sesuai total
        $this->actingAs($owner)->post(route('owner.tagihan.store'), [
            'kontrak_id' => $kontrak->id,
            'bill_type' => 'Sewa Bulanan',
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'subtotal' => 999999,
            'discount' => 0,
            'penalty' => 0,
            'due_date' => now()->addDays(10)->toDateString(),
        ])->assertRedirect();

        $tagihan = Tagihan::where('kontrak_id', $kontrak->id)->firstOrFail();
        // Subtotal dihitung server: 1 bulan x harga kontrak (1500000), bukan input client.
        $this->assertEquals(1500000, (float) $tagihan->subtotal);

        $proof = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');
        $this->actingAs($tenant)->post(route('tenant.pembayaran.store'), [
            'tagihan_id' => $tagihan->id,
            'amount' => $tagihan->total,
            'payment_method' => 'transfer_bank',
            'proof_file' => $proof,
        ])->assertRedirect();
        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id, 'status' => 'pending_verification']);

        // 5. Owner verify -> tagihan paid
        $pembayaran = Pembayaran::where('tagihan_id', $tagihan->id)->firstOrFail();
        $this->actingAs($owner)->post(route('owner.pembayaran.verify', $pembayaran))->assertRedirect();
        $this->assertDatabaseHas('tagihans', ['id' => $tagihan->id, 'status' => 'paid']);
        $this->assertDatabaseHas('pembayarans', ['id' => $pembayaran->id, 'verification_status' => 'approved']);

        // 6. Tenant ajukan check-out -> owner approve
        $this->actingAs($tenant)->post(route('tenant.checkout.request', $penghuni))->assertRedirect();
        $checkOut = CheckOut::where('penghuni_id', $penghuni->id)->firstOrFail();

        $this->actingAs($owner)->post(route('owner.checkout.approve', $checkOut))->assertSessionHas('success');

        // 7. Kamar available, penghuni inactive, kontrak terminated (keluar sebelum end_date)
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id, 'status' => 'available']);
        $this->assertDatabaseHas('penghunis', ['id' => $penghuni->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('kontraks', ['id' => $kontrak->id, 'status' => 'terminated']);
    }

    public function test_notifications_can_be_marked_all_read(): void
    {
        Mail::fake();

        $tenant = User::factory()->create(['role' => 'tenant']);
        Notification::create([
            'user_id' => $tenant->id,
            'type' => 'billing',
            'title' => 'Tagihan Baru',
            'message' => 'Test notifikasi',
        ]);

        $this->assertTrue($tenant->notifications()->where('is_read', false)->exists());

        $response = $this->actingAs($tenant)->post(route('notifications.markAllRead'));
        $response->assertRedirect();
        $this->assertFalse($tenant->notifications()->where('is_read', false)->exists());
    }
}
