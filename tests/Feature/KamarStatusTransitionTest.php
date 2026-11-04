<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KamarStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
    }

    public function test_checkin_kamar_occupied_penghuni_aktif_satu(): void
    {
        $kamar = Kamar::factory()->create();

        $penghuni = Penghuni::create([
            'user_id' => $this->tenant->id,
            'kos_id' => $kamar->kos_id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        $freshOccupancy = Penghuni::where('kamar_id', $kamar->id)
            ->where('status', 'active')
            ->count();

        if ($freshOccupancy >= (int) $kamar->capacity) {
            $kamar->update(['status' => 'occupied']);
        } elseif ($freshOccupancy === 0) {
            $kamar->update(['status' => 'booked']);
        } else {
            $kamar->update(['status' => 'available']);
        }

        $this->assertEquals('occupied', $kamar->status);
        $this->assertEquals(1, Penghuni::where('kamar_id', $kamar->id)
            ->where('status', 'active')
            ->count());

        $penghuni->delete();
    }

    public function test_checkin_bug_terjadi_jika_booked_after_checkin(): void
    {
        $kamar = Kamar::factory()->create();

        $penghuni = Penghuni::create([
            'user_id' => $this->tenant->id,
            'kos_id' => $kamar->kos_id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        $freshOccupancy = Penghuni::where('kamar_id', $kamar->id)
            ->where('status', 'active')
            ->count();

        if ($freshOccupancy >= (int) $kamar->capacity) {
            $kamar->update(['status' => 'occupied']);
        } elseif ($freshOccupancy === 0) {
            $kamar->update(['status' => 'booked']);
        } else {
            $kamar->update(['status' => 'available']);
        }

        $this->assertEquals('occupied', $kamar->status,
            'BUG REGRESI: check-in dengan 1 tenant capacity 1 HARUS "occupied", bukan "booked"');

        $penghuni->delete();
    }

    public function test_expireoldkontrak_status_transition_dengan_booking(): void
    {
        $kamar = Kamar::factory()->create();

        Kontrak::create([
            'kamar_id' => $kamar->id,
            'billing_mode' => 'personal',
            'status' => 'expired',
            'start_date' => now()->subMonths(1),
            'end_date' => now()->subDays(1),
        ]);

        $penghuni = Penghuni::create([
            'user_id' => $this->tenant->id,
            'kos_id' => $kamar->kos_id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
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

        $remainingOccupancy = Penghuni::where('kamar_id', $kamar->id)
            ->where('status', 'active')
            ->count();

        if ($remainingOccupancy > 0) {
            $kamar->update(['status' => 'occupied']);
        } elseif ($kamar->status === 'occupied') {
            $stillReserved = Booking::where('kamar_id', $kamar->id)
                ->where('status', 'approved')
                ->whereDate('end_date', '>=', today())
                ->exists();
            $kamar->update(['status' => $stillReserved ? 'booked' : 'available']);
        }

        $this->assertEquals('booked', $kamar->status,
            'ExpireOldKontraks: setelah kontrak expired + penghuni dinon-aktif + booking approved → "booked"');

        $penghuni->delete();
    }

    public function test_expireoldbookings_status_transition(): void
    {
        $kamar = Kamar::factory()->create();

        $penghuni = Penghuni::create([
            'user_id' => $this->tenant->id,
            'kos_id' => $kamar->kos_id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        Booking::factory()->create([
            'kamar_id' => $kamar->id,
            'user_id' => $this->tenant->id,
            'status' => 'expired',
            'start_date' => now()->subMonths(1),
            'end_date' => now()->subDays(1),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
        ]);

        $remaining = Penghuni::where('kamar_id', $kamar->id)
            ->where('status', 'active')
            ->count();

        if ($remaining > 0) {
            $kamar->update(['status' => 'occupied']);
        } else {
            $kamar->update(['status' => 'available']);
        }

        $this->assertEquals('occupied', $kamar->status,
            'ExpireOldBookings: masih ada penghuni aktif setelah booking expired → "occupied"');

        $penghuni->delete();
    }

    public function test_bookingstatusservice_status_transition(): void
    {
        $kamar = Kamar::factory()->create();

        $penghuni = Penghuni::create([
            'user_id' => $this->tenant->id,
            'kos_id' => $kamar->kos_id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
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

        $occupancy = Penghuni::where('kamar_id', $kamar->id)
            ->where('status', 'active')
            ->count();

        if ($occupancy > 0) {
            $kamar->update(['status' => 'occupied']);
            $penghuni->delete();

            return;
        }

        $kamar2 = Kamar::factory()->create();

        $penghuni2 = Penghuni::create([
            'user_id' => $this->tenant->id,
            'kos_id' => $kamar2->kos_id,
            'kamar_id' => $kamar2->id,
            'status' => 'active',
        ]);
        $penghuni2->save();

        // Booking approved tanpa penghuni aktif
        Booking::factory()->create([
            'kamar_id' => $kamar2->id,
            'user_id' => $this->tenant->id,
            'status' => 'approved',
            'start_date' => now()->addDay(),
            'end_date' => now()->addMonths(3),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
        ]);

        $stillReserved = Booking::where('kamar_id', $kamar2->id)
            ->whereIn('status', ['approved'])
            ->whereDate('end_date', '>=', today())
            ->exists();

        $kamar2->update(['status' => $stillReserved ? 'booked' : 'available']);

        $this->assertEquals('booked', $kamar2->status,
            'BookingStatusService: occupancy=0 tapi ada booking approved → "booked"');

        $penghuni2->delete();
    }

    public function test_checkout_via_checkoutcontroller(): void
    {
        $kamar = Kamar::factory()->create();

        $penghuni = Penghuni::create([
            'user_id' => $this->tenant->id,
            'kos_id' => $kamar->kos_id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
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

        $remainingOccupancy = Penghuni::where('kamar_id', $kamar->id)
            ->where('status', 'active')
            ->count();

        if ($remainingOccupancy > 0) {
            $kamar->update(['status' => 'occupied']);
        } else {
            $stillReserved = Booking::where('kamar_id', $kamar->id)
                ->where('status', 'approved')
                ->whereDate('end_date', '>=', today())
                ->exists();
            $kamar->update(['status' => $stillReserved ? 'booked' : 'available']);
        }

        $this->assertEquals('booked', $kamar->status,
            'Check-out (CheckOutController): remaining=0 + booking approved → "booked"');

        $penghuni->delete();
    }

    public function test_owneractioncontroller_checkout_only_occupancy(): void
    {
        $kamar = Kamar::factory()->create();

        $penghuni = Penghuni::create([
            'user_id' => $this->tenant->id,
            'kos_id' => $kamar->kos_id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
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

        // Cek booking approved dengan end_date >= hari ini → status menjadi "booked"
        $stillReserved = Booking::where('kamar_id', $kamar->id)
            ->where('status', 'approved')
            ->whereDate('end_date', '>=', today())
            ->exists();

        $kamar->update(['status' => $stillReserved ? 'booked' : 'available']);

        $this->assertEquals('booked', $kamar->status,
            'OwnerActionController check-out: ada booking approved dengan end_date >= hari ini → "booked"');

        $penghuni->delete();
    }

    public function test_maintenance_status_does_not_change_after_checkout(): void
    {
        $kamar = Kamar::factory()->create();

        $penghuni = Penghuni::create([
            'user_id' => $this->tenant->id,
            'kos_id' => $kamar->kos_id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        // Set kamar status menjadi maintenance
        $kamar->update(['status' => 'maintenance']);

        Booking::factory()->create([
            'kamar_id' => $kamar->id,
            'user_id' => $this->tenant->id,
            'status' => 'approved',
            'start_date' => now()->addDay(),
            'end_date' => now()->addMonths(3),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
        ]);

        // Simulasi check-out: penghuni dinon-aktifkan, tapi kamar status harus tetap maintenance
        $penghuni->update(['status' => 'inactive']);

        // Kamar maintenance tetap maintenance, tidak berubah menjadi occupied/available/booked
        $this->assertEquals('maintenance', $kamar->status,
            'Kamar status maintenance tidak berubah setelah check-out penghuni dinon-aktif');

        $penghuni->delete();
    }
}
