<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Kontrak perilaku transisi status kamar:
 * - ada penghuni aktif  -> occupied
 * - tanpa penghuni tapi ada booking approved aktif -> booked
 * - tanpa keduanya      -> available
 * - maintenance tidak pernah ditimpa
 */
class KamarStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'pencari']);
    }

    private function kamarDenganPenghuniAktif(string $status = 'occupied'): array
    {
        $kamar = Kamar::factory()->create(['status' => $status]);

        $penghuni = Penghuni::create([
            'user_id' => $this->tenant->id,
            'kos_id' => $kamar->kos_id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        return [$kamar, $penghuni];
    }

    public function test_checkin_kamar_occupied_penghuni_aktif_satu(): void
    {
        [$kamar] = $this->kamarDenganPenghuniAktif('available');

        $kamar->syncStatus();

        $this->assertSame('occupied', $kamar->fresh()->status);
        $this->assertSame(1, Penghuni::where('kamar_id', $kamar->id)
            ->where('status', 'active')
            ->count());
    }

    public function test_checkin_bug_terjadi_jika_booked_after_checkin(): void
    {
        [$kamar] = $this->kamarDenganPenghuniAktif('booked');

        $kamar->syncStatus();

        $this->assertSame('occupied', $kamar->fresh()->status,
            'BUG REGRESI: check-in dengan 1 tenant capacity 1 HARUS "occupied", bukan "booked"');
    }

    public function test_expireoldkontrak_status_transition_dengan_booking(): void
    {
        Mail::fake();

        [$kamar, $penghuni] = $this->kamarDenganPenghuniAktif('occupied');

        Kontrak::create([
            'contract_number' => 'KT-EXPIRE-1',
            'penghuni_id' => $penghuni->id,
            'kos_id' => $kamar->kos_id,
            'kamar_id' => $kamar->id,
            'rental_type' => 'monthly',
            'rental_price' => $kamar->monthly_price,
            'billing_mode' => 'personal',
            'status' => 'active',
            'start_date' => now()->subMonths(2),
            'end_date' => now()->subDay(),
        ]);

        Booking::factory()->create([
            'kamar_id' => $kamar->id,
            'user_id' => $this->tenant->id,
            'status' => 'approved',
            'start_date' => now()->addDay(),
            'end_date' => now()->addMonths(3),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
        ]);

        $this->artisan('kontrak:expire-old')->assertSuccessful();

        $this->assertSame('inactive', $penghuni->fresh()->status);
        $this->assertSame('booked', $kamar->fresh()->status,
            'ExpireOldKontraks: penghuni dinon-aktif + booking approved -> "booked"');
    }

    public function test_expireoldbookings_status_transition(): void
    {
        [$kamar] = $this->kamarDenganPenghuniAktif('occupied');

        Booking::factory()->create([
            'kamar_id' => $kamar->id,
            'user_id' => $this->tenant->id,
            'status' => 'approved',
            'start_date' => now()->subMonths(1),
            'end_date' => now()->subDay(),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
        ]);

        $this->artisan('booking:expire-old')->assertSuccessful();

        $this->assertSame('occupied', $kamar->fresh()->status,
            'ExpireOldBookings: masih ada penghuni aktif -> "occupied"');
    }

    public function test_bookingstatusservice_status_transition(): void
    {
        $kamar = Kamar::factory()->create(['status' => 'available']);

        Booking::factory()->create([
            'kamar_id' => $kamar->id,
            'user_id' => $this->tenant->id,
            'status' => 'approved',
            'start_date' => now()->addDay(),
            'end_date' => now()->addMonths(3),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
        ]);

        $kamar->syncStatus();

        $this->assertSame('booked', $kamar->fresh()->status,
            'BookingStatusService: occupancy=0 tapi ada booking approved -> "booked"');
    }

    public function test_checkout_via_checkoutcontroller(): void
    {
        [$kamar, $penghuni] = $this->kamarDenganPenghuniAktif('occupied');

        Booking::factory()->create([
            'kamar_id' => $kamar->id,
            'user_id' => $this->tenant->id,
            'status' => 'approved',
            'start_date' => now()->addDay(),
            'end_date' => now()->addMonths(3),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
        ]);

        // Simulasi CheckOutController: penghuni dinon-aktifkan lalu syncStatus.
        $penghuni->update(['status' => 'inactive']);
        $kamar->syncStatus();

        $this->assertSame('booked', $kamar->fresh()->status,
            'Check-out (CheckOutController): remaining=0 + booking approved -> "booked"');
    }

    public function test_owneractioncontroller_checkout_only_occupancy(): void
    {
        [$kamar, $penghuni] = $this->kamarDenganPenghuniAktif('occupied');

        // Tanpa booking approved: check-out melepas kamar menjadi available.
        $penghuni->update(['status' => 'inactive']);
        $kamar->syncStatus();

        $this->assertSame('available', $kamar->fresh()->status,
            'OwnerActionController check-out: tidak ada penghuni & booking -> "available"');
    }

    public function test_maintenance_status_does_not_change_after_checkout(): void
    {
        [$kamar, $penghuni] = $this->kamarDenganPenghuniAktif('maintenance');

        Booking::factory()->create([
            'kamar_id' => $kamar->id,
            'user_id' => $this->tenant->id,
            'status' => 'approved',
            'start_date' => now()->addDay(),
            'end_date' => now()->addMonths(3),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
        ]);

        $penghuni->update(['status' => 'inactive']);
        $kamar->syncStatus();

        $this->assertSame('maintenance', $kamar->fresh()->status,
            'Kamar maintenance tidak berubah setelah check-out penghuni');
    }
}
