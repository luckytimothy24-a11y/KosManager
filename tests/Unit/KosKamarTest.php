<?php

namespace Tests\Unit;

use App\Models\Kamar;
use App\Models\Kos;
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
}
