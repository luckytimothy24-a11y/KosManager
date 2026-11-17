<?php

namespace Tests\Feature;

use App\Models\MarketplaceItem;
use App\Models\User;
use Database\Seeders\MarketplaceDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_eight_available_demo_items_without_duplicates(): void
    {
        User::factory()->create(['role' => 'pencari']);

        $this->seed(MarketplaceDemoSeeder::class);

        $this->assertDatabaseCount('marketplace_items', 8);
        $this->assertSame(8, MarketplaceItem::where('status', MarketplaceItem::STATUS_AVAILABLE)->count());
        $this->assertDatabaseHas('marketplace_items', [
            'title' => 'Kipas Angin Miyako 12 inch',
            'price' => 150000,
            'condition' => MarketplaceItem::CONDITION_USED,
            'category' => MarketplaceItem::CATEGORY_ELECTRONICS,
        ]);

        // Seeder harus aman dipanggil ulang oleh `db:seed` tanpa menggandakan data demo.
        $this->seed(MarketplaceDemoSeeder::class);

        $this->assertDatabaseCount('marketplace_items', 8);
    }
}
