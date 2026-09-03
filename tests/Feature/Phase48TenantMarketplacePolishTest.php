<?php

namespace Tests\Feature;

use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase48TenantMarketplacePolishTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
    }

    private function cardKos(string $name): Kos
    {
        return Kos::factory()->create(['owner_id' => $this->owner->id, 'status' => 'active', 'name' => $name]);
    }

    private function attachKamarFacilities(Kos $kos, array $names): void
    {
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $facilities = collect($names)->map(fn ($n) => Fasilitas::factory()->create(['name' => $n]));
        $kamar->fasilitas()->attach($facilities->pluck('id')->all());
    }

    // ── Phase 4.8 · Reusable single marketplace card ──

    public function test_shared_kos_card_used_on_dashboard_and_discovery(): void
    {
        $kos = $this->cardKos('Kos Satu Kartu');
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $dashboard = $this->actingAs($this->tenant)->get('/dashboard');
        $dashboard->assertOk();
        $dashboard->assertSee('aria-label="Lihat detail Kos Satu Kartu"', false);

        $index = $this->actingAs($this->tenant)->get(route('tenant.kos.index'));
        $index->assertOk();
        $index->assertSee('aria-label="Lihat detail Kos Satu Kartu"', false);
    }

    public function test_kos_card_shows_plus_n_facilities_when_more_than_three(): void
    {
        $kos = $this->cardKos('Kos Fasilitas Banyak');
        $this->attachKamarFacilities($kos, ['WiFi', 'AC', 'Parkir', 'CCTV', 'Dapur']);

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Kos Fasilitas Banyak');
        $response->assertSee('WiFi');
        $response->assertSee('+2 fasilitas');
    }

    public function test_kos_card_shows_marketplace_facility_separator(): void
    {
        $kos = $this->cardKos('Kos Dot');
        $this->attachKamarFacilities($kos, ['WiFi', 'AC']);

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Kos Dot');
        // dot separator between facilities
        $response->assertSee('<span class="mx-1 text-slate-300 dark:text-slate-600">', false);
    }

    public function test_kos_card_shows_price_and_detail_cta(): void
    {
        $kos = $this->cardKos('Kos Harga');
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'monthly_price' => 500000]);

        $response = $this->actingAs($this->tenant)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Kos Harga');
        $response->assertSee('Mulai dari');
        $response->assertSee('Lihat Detail');
        $response->assertSee('/bln', false);
    }

    // ── Phase 4.8 · Kos detail room section subtitle ──

    public function test_kos_detail_room_section_has_subtitle(): void
    {
        $kos = $this->cardKos('Kos Detail 48');
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);

        $response = $this->actingAs($this->tenant)->get(route('tenant.kos.show', $kos));

        $response->assertOk();
        $response->assertSee('Pilihan Kamar');
        $response->assertSee('Temukan kamar yang sesuai kebutuhan dan budgetmu.');
    }
}
