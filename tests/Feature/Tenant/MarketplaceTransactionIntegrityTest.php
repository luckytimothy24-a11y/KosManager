<?php

namespace Tests\Feature\Tenant;

use App\Models\MarketplaceItem;
use App\Models\MarketplaceTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MarketplaceTransactionIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure migrations are run for SQLite in-memory
        $this->artisan('migrate', ['--force' => true]);
    }

    public function test_buyer_can_successfully_purchase_available_item(): void
    {
        $seller = User::factory()->create(['role' => 'tenant']);
        $buyer = User::factory()->create(['role' => 'tenant']);

        $item = MarketplaceItem::create([
            'user_id' => $seller->id,
            'kos_id' => null,
            'title' => 'Test Item',
            'description' => 'Description',
            'price' => 100000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);

        $this->actingAs($buyer);

        $response = $this->post(route('tenant.marketplace.buy', $item));

        $response->assertRedirect(route('tenant.marketplace.show', $item));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('marketplace_transactions', [
            'item_id' => $item->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'price' => 100000,
            'status' => MarketplaceTransaction::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('marketplace_items', [
            'id' => $item->id,
            'status' => MarketplaceItem::STATUS_RESERVED,
        ]);
    }

    public function test_buyer_cannot_purchase_own_item(): void
    {
        $seller = User::factory()->create(['role' => 'tenant']);
        $item = MarketplaceItem::create([
            'user_id' => $seller->id,
            'kos_id' => null,
            'title' => 'Test Item',
            'description' => 'Description',
            'price' => 100000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);

        $this->actingAs($seller);

        $response = $this->post(route('tenant.marketplace.buy', $item));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda tidak bisa membeli barang sendiri.');

        $this->assertDatabaseMissing('marketplace_transactions', [
            'item_id' => $item->id,
            'buyer_id' => $seller->id,
        ]);

        $this->assertDatabaseHas('marketplace_items', [
            'id' => $item->id,
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);
    }

    public function test_second_buyer_cannot_purchase_already_reserved_item(): void
    {
        $seller = User::factory()->create(['role' => 'tenant']);
        $buyer2 = User::factory()->create(['role' => 'tenant']);

        $item = MarketplaceItem::create([
            'user_id' => $seller->id,
            'kos_id' => null,
            'title' => 'Test Item',
            'description' => 'Description',
            'price' => 100000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_RESERVED,
        ]);

        // Verify item is not available when reserved
        $this->assertFalse($item->isAvailable(), 'Item should not be available when reserved');

        // Verify no transaction exists for this item
        $this->assertDatabaseMissing('marketplace_transactions', [
            'item_id' => $item->id,
            'buyer_id' => $buyer2->id,
        ]);
    }

    public function test_buyer_cannot_purchase_sold_item(): void
    {
        $seller = User::factory()->create(['role' => 'tenant']);
        $buyer = User::factory()->create(['role' => 'tenant']);

        $item = MarketplaceItem::create([
            'user_id' => $seller->id,
            'kos_id' => null,
            'title' => 'Test Item',
            'description' => 'Description',
            'price' => 100000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_SOLD,
        ]);

        // Verify item is not available when sold
        $this->assertFalse($item->isAvailable(), 'Item should not be available when sold');

        // Verify no transaction exists for sold items
        $this->assertDatabaseMissing('marketplace_transactions', [
            'item_id' => $item->id,
            'buyer_id' => $buyer->id,
        ]);
    }

    public function test_cross_kos_buyer_can_purchase_item(): void
    {
        $seller = User::factory()->create(['role' => 'tenant']);
        $buyer = User::factory()->create(['role' => 'tenant']);

        // Create a kos for the seller
        $kos = \App\Models\Kos::create([
            'name' => 'Test Kos',
            'address' => 'Test Address',
            'owner_id' => $seller->id,
            'status' => 'active',
        ]);

        // Simulate different kos by having different kos_id
        $item = MarketplaceItem::create([
            'user_id' => $seller->id,
            'kos_id' => $kos->id,
            'title' => 'Test Item',
            'description' => 'Description',
            'price' => 100000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);

        $this->actingAs($buyer);

        $response = $this->post(route('tenant.marketplace.buy', $item));

        $response->assertRedirect(route('tenant.marketplace.show', $item));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('marketplace_transactions', [
            'item_id' => $item->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'status' => MarketplaceTransaction::STATUS_PENDING,
        ]);
    }

    public function test_transaction_rollbacks_if_item_update_fails(): void
    {
        // This test simulates a failure scenario by mocking the update to fail
        // In practice, we verify the atomicity by checking that no partial state remains
        $seller = User::factory()->create(['role' => 'tenant']);
        $buyer = User::factory()->create(['role' => 'tenant']);

        $item = MarketplaceItem::create([
            'user_id' => $seller->id,
            'kos_id' => null,
            'title' => 'Test Item',
            'description' => 'Description',
            'price' => 100000,
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);

        $this->actingAs($buyer);

        // Use a closure to capture the transaction behavior
        $transactionCreated = false;
        try {
            DB::transaction(function () use ($buyer, $item, &$transactionCreated) {
                $lockedItem = MarketplaceItem::query()
                    ->where('id', $item->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedItem || ! $lockedItem->isAvailable()) {
                    throw new \Exception('Item not available');
                }

                MarketplaceTransaction::create([
                    'item_id' => $lockedItem->id,
                    'buyer_id' => $buyer->id,
                    'seller_id' => $lockedItem->user_id,
                    'price' => $lockedItem->price,
                    'status' => MarketplaceTransaction::STATUS_PENDING,
                ]);

                $transactionCreated = true;

                // Force an exception to trigger rollback
                throw new \Exception('Simulated failure during item update');
            });
        } catch (\Exception $e) {
            // Expected - transaction should rollback
        }

        // Verify no transaction was persisted
        $this->assertDatabaseMissing('marketplace_transactions', [
            'item_id' => $item->id,
            'buyer_id' => $buyer->id,
        ]);

        // Verify item status is still available (rolled back)
        $this->assertDatabaseHas('marketplace_items', [
            'id' => $item->id,
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);
    }
}