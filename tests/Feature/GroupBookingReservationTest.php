<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupBookingReservationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $tenantA;
    private Kos $kos;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenantA = User::factory()->create(['role' => 'tenant']);
        $this->kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active']);
    }

    private function sharedKamar(int $capacity, int $price = 1200000): Kamar
    {
        return Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'available',
            'capacity' => $capacity,
            'billing_mode' => 'shared',
            'monthly_price' => $price,
            'daily_price' => 50000,
            'weekly_price' => 350000,
        ]);
    }

    private function payload(Kamar $kamar, int $requested, string $billingMode = 'shared'): array
    {
        return [
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => $billingMode,
            'requested_occupants' => $requested,
        ];
    }

    public function test_solo_booking_requested_one(): void
    {
        $kamar = $this->sharedKamar(3);
        $svc = app(BookingService::class);
        $r = $svc->create($this->payload($kamar, 1), $this->tenantA->id);
        $this->assertTrue($r['ok']);
        $this->assertSame(1, (int) $r['booking']->requested_occupants);
    }

    public function test_booking_two_people(): void
    {
        $kamar = $this->sharedKamar(3);
        $svc = app(BookingService::class);
        $r = $svc->create($this->payload($kamar, 2), $this->tenantA->id);
        $this->assertTrue($r['ok']);
        $this->assertSame(2, (int) $r['booking']->requested_occupants);
    }

    public function test_booking_three_people(): void
    {
        $kamar = $this->sharedKamar(3);
        $svc = app(BookingService::class);
        $r = $svc->create($this->payload($kamar, 3), $this->tenantA->id);
        $this->assertTrue($r['ok']);
        $this->assertSame(3, (int) $r['booking']->requested_occupants);
    }

    public function test_capacity_two_request_three_rejected(): void
    {
        $kamar = $this->sharedKamar(2);
        $svc = app(BookingService::class);
        $r = $svc->create($this->payload($kamar, 3), $this->tenantA->id);
        $this->assertFalse($r['ok']);
        $this->assertSame('capacity_exceeded', $r['error']);
    }

    public function test_capacity_three_request_four_rejected(): void
    {
        $kamar = $this->sharedKamar(3);
        $svc = app(BookingService::class);
        $r = $svc->create($this->payload($kamar, 4), $this->tenantA->id);
        $this->assertFalse($r['ok']);
        $this->assertSame('capacity_exceeded', $r['error']);
    }

    public function test_capacity_three_empty_request_three_pass(): void
    {
        $kamar = $this->sharedKamar(3);
        $svc = app(BookingService::class);
        $r = $svc->create($this->payload($kamar, 3), $this->tenantA->id);
        $this->assertTrue($r['ok']);
    }

    public function test_existing_reservation_two_plus_request_one_pass(): void
    {
        $kamar = $this->sharedKamar(3);
        $svc = app(BookingService::class);
        $t1 = User::factory()->create(['role' => 'tenant']);
        $r1 = $svc->create($this->payload($kamar, 2), $t1->id);
        $this->assertTrue($r1['ok']);
        $r2 = $svc->create($this->payload($kamar, 1), $this->tenantA->id);
        $this->assertTrue($r2['ok']);
    }

    public function test_existing_reservation_two_plus_request_two_reject(): void
    {
        $kamar = $this->sharedKamar(3);
        $svc = app(BookingService::class);
        $t1 = User::factory()->create(['role' => 'tenant']);
        $svc->create($this->payload($kamar, 2), $t1->id);
        $r2 = $svc->create($this->payload($kamar, 2), $this->tenantA->id);
        $this->assertFalse($r2['ok']);
        $this->assertSame('kamar_penuh', $r2['error']);
    }

    public function test_occupancy_two_plus_request_one_pass(): void
    {
        $kamar = $this->sharedKamar(3);
        // 2 active penghuni
        foreach (range(1, 2) as $i) {
            $u = User::factory()->create(['role' => 'tenant']);
            $p = Penghuni::factory()->create(['user_id' => $u->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
            \App\Models\Kontrak::factory()->create(['penghuni_id' => $p->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'billing_mode' => 'shared', 'status' => 'active']);
        }
        $kamar->update(['status' => 'occupied']);
        $svc = app(BookingService::class);
        $r = $svc->create($this->payload($kamar, 1), $this->tenantA->id);
        $this->assertTrue($r['ok']);
    }

    public function test_occupancy_two_plus_request_two_reject(): void
    {
        $kamar = $this->sharedKamar(3);
        foreach (range(1, 2) as $i) {
            $u = User::factory()->create(['role' => 'tenant']);
            $p = Penghuni::factory()->create(['user_id' => $u->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'status' => 'active']);
            \App\Models\Kontrak::factory()->create(['penghuni_id' => $p->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id, 'billing_mode' => 'shared', 'status' => 'active']);
        }
        $kamar->update(['status' => 'occupied']);
        $svc = app(BookingService::class);
        $r = $svc->create($this->payload($kamar, 2), $this->tenantA->id);
        $this->assertFalse($r['ok']);
        $this->assertSame('kamar_penuh', $r['error']);
    }

    public function test_cancelled_booking_releases_slots(): void
    {
        $kamar = $this->sharedKamar(3);
        $svc = app(BookingService::class);
        $t1 = User::factory()->create(['role' => 'tenant']);
        $r1 = $svc->create($this->payload($kamar, 3), $t1->id);
        $this->assertTrue($r1['ok']);
        $svc->cancel($r1['booking']->id);
        $r2 = $svc->create($this->payload($kamar, 3), $this->tenantA->id);
        $this->assertTrue($r2['ok']);
    }

    public function test_expired_booking_releases_slots(): void
    {
        $kamar = $this->sharedKamar(3);
        $svc = app(BookingService::class);
        $t1 = User::factory()->create(['role' => 'tenant']);
        $b = Booking::factory()->create([
            'user_id' => $t1->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id,
            'status' => 'approved', 'requested_occupants' => 2,
            'start_date' => now()->subDays(10), 'end_date' => now()->subDay(),
        ]);
        // Expired via command logic: mark as expired
        $b->update(['status' => 'expired']);
        $r = $svc->create($this->payload($kamar, 3), $this->tenantA->id);
        $this->assertTrue($r['ok']);
    }

    public function test_rejected_booking_releases_slots(): void
    {
        $kamar = $this->sharedKamar(3);
        $t1 = User::factory()->create(['role' => 'tenant']);
        Booking::factory()->create([
            'user_id' => $t1->id, 'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id,
            'status' => 'rejected', 'requested_occupants' => 3,
            'start_date' => now()->addDays(5), 'end_date' => now()->addMonths(2),
        ]);
        $svc = app(BookingService::class);
        $r = $svc->create($this->payload($kamar, 3), $this->tenantA->id);
        $this->assertTrue($r['ok']);
    }

    public function test_personal_billing_with_requested_two_rejected(): void
    {
        $kamar = $this->sharedKamar(3); // kamar shared but request personal+2
        $svc = app(BookingService::class);
        $r = $svc->create($this->payload($kamar, 2, 'personal'), $this->tenantA->id);
        $this->assertFalse($r['ok']);
        $this->assertSame('personal_multi_rejected', $r['error']);
    }

    public function test_shared_billing_with_requested_two_accepted(): void
    {
        $kamar = $this->sharedKamar(3);
        $svc = app(BookingService::class);
        $r = $svc->create($this->payload($kamar, 2, 'shared'), $this->tenantA->id);
        $this->assertTrue($r['ok']);
    }

    public function test_shared_billing_with_requested_three_accepted(): void
    {
        $kamar = $this->sharedKamar(3);
        $svc = app(BookingService::class);
        $r = $svc->create($this->payload($kamar, 3, 'shared'), $this->tenantA->id);
        $this->assertTrue($r['ok']);
    }

    public function test_existing_old_booking_defaults_to_one(): void
    {
        $kamar = $this->sharedKamar(3);
        // Create booking without explicit requested_occupants via factory (should default 1)
        $b = Booking::factory()->create([
            'kos_id' => $this->kos->id, 'kamar_id' => $kamar->id,
            'status' => 'approved',
            'start_date' => now()->addDays(5), 'end_date' => now()->addMonths(2),
        ]);
        $this->assertSame(1, (int) ($b->requested_occupants ?? 1));
        // Reservation should count as 1
        $svc = app(BookingService::class);
        $r = $svc->create($this->payload($kamar, 2), $this->tenantA->id);
        $this->assertTrue($r['ok']); // 1 reserved +2 new =3 <=3
        $r2 = User::factory()->create(['role' => 'tenant']);
        $rFail = $svc->create($this->payload($kamar, 1), $r2->id);
        $this->assertFalse($rFail['ok']); // 1+2+1=4 >3
    }

    public function test_existing_capacity_one_behavior_unchanged(): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'available', 'capacity' => 1, 'billing_mode' => 'personal', 'monthly_price' => 1000000, 'daily_price' => 50000, 'weekly_price' => 300000]);
        $svc = app(BookingService::class);
        $r1 = $svc->create([
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
            'requested_occupants' => 1,
        ], $this->tenantA->id);
        $this->assertTrue($r1['ok']);
        $t2 = User::factory()->create(['role' => 'tenant']);
        $r2 = $svc->create([
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'rental_type' => 'monthly',
            'billing_mode' => 'personal',
            'requested_occupants' => 1,
        ], $t2->id);
        $this->assertFalse($r2['ok']);
    }

    public function test_requested_occupants_does_not_create_penghuni(): void
    {
        $kamar = $this->sharedKamar(3);
        $svc = app(BookingService::class);
        $svc->create($this->payload($kamar, 3), $this->tenantA->id);
        $this->assertSame(0, Penghuni::where('kamar_id', $kamar->id)->count());
        $this->assertSame(0, $kamar->occupancy());
    }
}
