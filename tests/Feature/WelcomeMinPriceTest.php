<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomeMinPriceTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_min_price_only_considers_available_rooms(): void
    {
        $kos = Kos::factory()->create(['name' => 'Kos Amethyst', 'status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'monthly_price' => 1500000]);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'booked', 'monthly_price' => 500000]);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'maintenance', 'monthly_price' => 750000]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Kos Amethyst');
        $response->assertSee('Rp 1.500.000');
        $response->assertDontSee('Rp 500.000');
        $response->assertDontSee('Rp 750.000');
    }

    public function test_landing_shows_no_fake_price_when_no_room_is_available(): void
    {
        $kos = Kos::factory()->create(['name' => 'Kos Penuh', 'status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'booked', 'monthly_price' => 500000]);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'maintenance', 'monthly_price' => 750000]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Kos Penuh');
        $response->assertSee('PENUH');
        $response->assertSee('Harga hubungi pemilik');
        $response->assertDontSee('Rp 500.000');
        $response->assertDontSee('Rp 750.000');
    }
}
