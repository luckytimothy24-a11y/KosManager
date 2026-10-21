<?php

namespace Tests\Unit;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KosKamarTest extends TestCase
{
    use RefreshDatabase;

    public function test_kos_has_correct_relationships(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id]);

        $this->assertEquals($owner->id, $kos->owner_id);
        $this->assertTrue($kos->owner->is($owner));
    }

    public function test_kamar_belongs_to_kos(): void
    {
        $kos = Kos::factory()->create();
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);

        $this->assertTrue($kamar->kos->is($kos));
    }

    public function test_kamar_status_values(): void
    {
        $validStatuses = ['available', 'booked', 'occupied', 'maintenance'];

        foreach ($validStatuses as $status) {
            $kamar = Kamar::factory()->create(['status' => $status]);
            $this->assertEquals($status, $kamar->fresh()->status);
        }
    }

    public function test_kos_has_many_kamar(): void
    {
        $kos = Kos::factory()->create();
        Kamar::factory()->count(3)->create(['kos_id' => $kos->id]);

        $this->assertCount(3, $kos->kamar);
    }

    public function test_kamar_daily_and_monthly_price(): void
    {
        $kamar = Kamar::factory()->create([
            'daily_price' => 150000,
            'monthly_price' => 1500000,
        ]);

        $this->assertEquals(150000, $kamar->daily_price);
        $this->assertEquals(1500000, $kamar->monthly_price);
    }

    public function test_kamar_capacity_is_integer(): void
    {
        $kamar = Kamar::factory()->create(['capacity' => 3]);
        $this->assertEquals(3, $kamar->capacity);
        $this->assertIsInt($kamar->capacity);
    }

    public function test_kamar_capacity_minimum_is_one(): void
    {
        $kamar = Kamar::factory()->create(['capacity' => 1]);
        $this->assertEquals(1, $kamar->capacity);
    }

    public function test_kamar_remaining_slots_calculates_correctly(): void
    {
        $kamar = Kamar::factory()->create(['capacity' => 4]);
        Penghuni::factory()->count(2)->create(['kamar_id' => $kamar->id, 'status' => 'active']);
        $this->assertEquals(2, $kamar->remainingSlots());
        $this->assertTrue($kamar->hasAvailableSlot());
    }

    public function test_kamar_is_full_when_capacity_reached(): void
    {
        $kamar = Kamar::factory()->create(['capacity' => 2]);
        Penghuni::factory()->count(2)->create(['kamar_id' => $kamar->id, 'status' => 'active']);
        $this->assertTrue($kamar->isFull());
    }

    public function test_kamar_is_not_full_when_capacity_not_reached(): void
    {
        $kamar = Kamar::factory()->create(['capacity' => 4]);
        Penghuni::factory()->count(1)->create(['kamar_id' => $kamar->id, 'status' => 'active']);
        $this->assertFalse($kamar->isFull());
    }

    public function test_kamar_occupancy_count_is_zero_when_no_occupants(): void
    {
        $kamar = Kamar::factory()->create(['capacity' => 3]);
        $this->assertEquals(0, $kamar->occupancy());
    }

    public function test_kamar_scope_with_available_slot_for_shared(): void
    {
        $kamar = Kamar::factory()->create(['capacity' => 4, 'billing_mode' => 'shared', 'status' => 'occupied']);
        Penghuni::factory()->count(1)->create(['kamar_id' => $kamar->id, 'status' => 'active']);
        $query = Kamar::withAvailableSlot()->where('id', $kamar->id);
        $results = $query->get();
        $this->assertCount(1, $results);
    }

    public function test_kamar_is_available_for_booking_when_shared_has_slot(): void
    {
        $kamar = Kamar::factory()->create(['capacity' => 4, 'billing_mode' => 'shared', 'status' => 'occupied']);
        Penghuni::factory()->count(1)->create(['kamar_id' => $kamar->id, 'status' => 'active']);
        $this->assertTrue($kamar->isAvailableForBooking());
    }
}
