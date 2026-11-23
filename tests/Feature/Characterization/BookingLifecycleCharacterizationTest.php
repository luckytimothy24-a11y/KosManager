<?php

namespace Tests\Feature\Characterization;

use App\Models\Booking;
use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * FASE 0 — KARAKTERISASI ALUR BOOKING -> CHECK-IN -> CHECK-OUT.
 *
 * Alur pembayaran penuh/sebagian/ditolak ada di PaymentFlowCharacterizationTest.
 * Catatan: deposit keamanan belum ada di baseline; test di sini mengunci
 * ketiadaannya agar tidak dikira sudah terimplementasi.
 */
class BookingLifecycleCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    private Kos $kos;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Storage::fake('private');
        Storage::fake('public');

        $this->owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $this->tenant = User::factory()->create(['role' => 'pencari', 'is_active' => true]);
        $this->kos = Kos::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'active',
            'auto_billing_enabled' => false,
        ]);
        $this->kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'available',
            'monthly_price' => 1500000,
            'weekly_price' => 400000,
            'daily_price' => 100000,
        ]);
    }

    public function test_booking_to_checkin_current_state_machine(): void
    {
        $this->actingAs($this->tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $this->kos->id,
            'kamar_id' => $this->kamar->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'rental_type' => 'monthly',
            'phone' => '081234567890',
            'address' => 'Jl. Karakterisasi No. 1',
        ])->assertRedirect();

        $booking = Booking::where('user_id', $this->tenant->id)->firstOrFail();
        $this->assertSame('pending', $booking->status);
        $this->assertEquals(100000, (float) $booking->down_payment_amount);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'booked']);
        $this->assertDatabaseMissing('penghunis', ['user_id' => $this->tenant->id, 'status' => 'active']);

        // DP dibayar dan diverifikasi owner; booking TETAP pending.
        $pay = app(PaymentService::class)->createForBooking($booking, $this->tenant, 'qris');
        $this->assertTrue($pay['ok'], $pay['message'] ?? 'payment failed');
        $this->actingAs($this->owner)->post(route('owner.pembayaran.verify', $pay['pembayaran']))->assertRedirect();
        $this->assertSame('pending', $booking->fresh()->status);

        $this->actingAs($this->owner)->post(route('owner.booking.approve', $booking))->assertRedirect();
        $this->assertSame('approved', $booking->fresh()->status);

        // Tenant mengirim kontak; owner tetap pemilik keputusan konfirmasi.
        $this->actingAs($this->tenant)->post(route('tenant.checkin.identity.confirm', $booking), [
            'phone' => '081234567890',
            'address' => 'Jl. Karakterisasi No. 1',
        ])->assertRedirect();
        $this->assertSame('pending', $booking->fresh()->identityStatus());
        $this->assertNull($booking->fresh()->identity_number);

        $this->actingAs($this->owner)->post(route('owner.checkin.confirm', $booking))->assertRedirect();
        $this->assertTrue($booking->fresh()->identityConfirmed());

        $this->actingAs($this->owner)->post(route('owner.checkin.process', $booking), [
            'notes' => 'Check-in karakterisasi',
        ])->assertRedirect();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'completed']);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'occupied']);

        $penghuni = Penghuni::where('user_id', $this->tenant->id)->firstOrFail();
        $this->assertSame('active', $penghuni->status);
        $this->assertSame('active', Kontrak::where('penghuni_id', $penghuni->id)->firstOrFail()->status);
    }

    public function test_checkout_is_blocked_while_any_bill_is_outstanding(): void
    {
        [$penghuni, $kontrak] = $this->makeActiveTenant();

        Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'unpaid',
            'subtotal' => 1000000,
            'discount' => 0,
            'penalty' => 0,
            'total' => 1000000,
        ]);

        $this->actingAs($this->tenant)->post(route('tenant.checkout.request', $penghuni))->assertRedirect();
        $checkOut = CheckOut::where('penghuni_id', $penghuni->id)->firstOrFail();

        $this->actingAs($this->owner)
            ->post(route('owner.checkout.approve', $checkOut))
            ->assertSessionHas('error');

        $this->assertSame('pending', $checkOut->fresh()->status);
        $this->assertSame('active', $penghuni->fresh()->status);
    }

    public function test_approved_checkout_terminates_contract_and_frees_room(): void
    {
        [$penghuni, $kontrak] = $this->makeActiveTenant();

        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $this->kamar->id,
            'status' => 'paid',
            'subtotal' => 1000000,
            'discount' => 0,
            'penalty' => 0,
            'total' => 1000000,
        ]);

        // Disetujui sebelum end_date => terminated (bukan expired).
        $this->actingAs($this->tenant)->post(route('tenant.checkout.request', $penghuni))->assertRedirect();
        $checkOut = CheckOut::where('penghuni_id', $penghuni->id)->firstOrFail();

        $this->actingAs($this->owner)
            ->post(route('owner.checkout.approve', $checkOut))
            ->assertSessionHas('success');

        $this->assertSame('approved', $checkOut->fresh()->status);
        $this->assertSame('inactive', $penghuni->fresh()->status);
        $this->assertSame('terminated', $kontrak->fresh()->status);
        $this->assertDatabaseHas('kamar', ['id' => $this->kamar->id, 'status' => 'available']);
        $this->assertSame('paid', $tagihan->fresh()->status);
    }

    public function test_security_deposit_and_deduction_are_not_implemented_yet(): void
    {
        // karakterisasi, bukan perilaku yang diinginkan.
        $this->assertFalse(Schema::hasTable('deposits'));
        $this->assertFalse(Schema::hasColumn('check_outs', 'deposit_amount'));
        $this->assertFalse(Schema::hasColumn('check_outs', 'deposit_deduction'));
        $this->assertFalse(Schema::hasColumn('check_outs', 'deposit_refund'));
        $this->assertFalse(Schema::hasColumn('kontraks', 'deposit_amount'));

        [$penghuni] = $this->makeActiveTenant();
        $this->actingAs($this->tenant)->post(route('tenant.checkout.request', $penghuni));
        $checkOut = CheckOut::where('penghuni_id', $penghuni->id)->firstOrFail();

        // Field uang deposit tidak ada; checkout hanya mencatat kondisi & catatan.
        $this->assertArrayNotHasKey('deposit_amount', $checkOut->getAttributes());
        $this->assertArrayNotHasKey('deposit_deduction', $checkOut->getAttributes());
    }

    public function test_bill_revision_endpoint_and_history_table_do_not_exist_yet(): void
    {
        // karakterisasi, bukan perilaku yang diinginkan.
        $this->assertFalse(Route::has('owner.tagihan.update'));
        $this->assertFalse(Route::has('admin.tagihan.update'));
        $this->assertFalse(Schema::hasTable('bill_revisions'));

        [$penghuni, $kontrak] = $this->makeActiveTenant();
        $tagihan = Tagihan::factory()->create([
            'penghuni_id' => $penghuni->id,
            'kontrak_id' => $kontrak->id,
            'kamar_id' => $this->kamar->id,
            'subtotal' => 1000000,
            'total' => 1000000,
        ]);

        $this->actingAs($this->owner)
            ->put(route('owner.tagihan.show', $tagihan), ['total' => 1])
            ->assertStatus(405);

        $this->assertEquals(1000000, (float) $tagihan->fresh()->total);
    }

    /**
     * @return array{0: Penghuni, 1: Kontrak}
     */
    private function makeActiveTenant(): array
    {
        $this->kamar->update(['status' => 'occupied']);

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
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonths(5)->toDateString(),
            'status' => 'active',
        ]);

        return [$penghuni, $kontrak];
    }
}
