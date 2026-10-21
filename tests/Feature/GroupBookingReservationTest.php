<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
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

    private function personalKamar(int $price = 1200000): Kamar
    {
        return Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'available',
            'capacity' => 1,
            'billing_mode' => 'personal',
            'monthly_price' => $price,
            'daily_price' => 50000,
            'weekly_price' => 350000,
        ]);
    }

    private function payload(Kamar $kamar, int $requested = 1, string $billingMode = 'personal'): array
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
        $kamar = $this->personalKamar();
        $svc = app(BookingService::class);
        $r = $svc->create($this->payload($kamar, 1), $this->tenantA->id);
        $this->assertTrue($r['ok']);
        $this->assertSame(1, (int) $r['booking']->requested_occupants);
    }

    public function test_personal_billing_with_requested_two_rejected(): void
    {
        $kamar = $this->personalKamar();
        $svc = app(BookingService::class);
        $r = $svc->create($this->payload($kamar, 2, 'personal'), $this->tenantA->id);
        $this->assertFalse($r['ok']);
        $this->assertSame('personal_multi_rejected', $r['error']);
    }

    public function test_shared_billing_rejected(): void
    {
        $kamar = $this->personalKamar();
        $svc = app(BookingService::class);
        $r = $svc->create($this->payload($kamar, 1, 'shared'), $this->tenantA->id);
        $this->assertFalse($r['ok']);
        $this->assertSame('billing_mode_not_allowed', $r['error']);
    }

    public function test_existing_capacity_one_behavior_unchanged(): void
    {
        $kamar = Kamar::factory()->create([
            'kos_id' => $this->kos->id,
            'status' => 'available',
            'capacity' => 1,
            'billing_mode' => 'personal',
            'monthly_price' => 1000000,
            'daily_price' => 50000,
            'weekly_price' => 300000,
        ]);
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
}
