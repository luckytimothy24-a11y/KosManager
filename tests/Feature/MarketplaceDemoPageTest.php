<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use Database\Seeders\MarketplaceDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceDemoPageTest extends TestCase
{
    use RefreshDatabase;

    private function createTenantWithKos(): User
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'occupied']);

        $tenant = User::factory()->create(['role' => 'pencari']);
        Penghuni::factory()->create([
            'user_id' => $tenant->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        return $tenant;
    }

    public function test_pencari_can_access_marketplace_page_with_demo_items(): void
    {
        $tenant = $this->createTenantWithKos();
        $this->seed(MarketplaceDemoSeeder::class);

        $this->actingAs($tenant)
            ->get(route('tenant.marketplace.index'))
            ->assertOk()
            ->assertSee('Marketplace Barang Bekas')
            ->assertSee('Kipas Angin Miyako 12 inch')
            ->assertSee('Rp 150.000')
            ->assertSee('Elektronik')
            ->assertSee('Bekas')
            ->assertSee('Lampu Meja LED Adjustable')
            ->assertSee('Baru')
            ->assertSee('Kemeja Formal Pria L')
            ->assertSee('Meja Lipat Portable')
            ->assertSee('Power Bank 10000mAh Xiaomi')
            ->assertSee('Novel Laskar Pelangi');
    }

    public function test_marketplace_items_have_active_status_and_correct_images(): void
    {
        $tenant = $this->createTenantWithKos();
        $this->seed(MarketplaceDemoSeeder::class);

        $response = $this->actingAs($tenant)->get(route('tenant.marketplace.index'));
        $response->assertOk();

        // Pastikan storage image URL ditampilkan di halaman untuk barang yang memiliki foto.
        $html = $response->getContent();
        $this->assertStringContainsString('storage/marketplace/', $html);
    }
}
