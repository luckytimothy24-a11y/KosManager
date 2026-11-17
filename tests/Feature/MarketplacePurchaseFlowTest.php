<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\MarketplaceItem;
use App\Models\MarketplaceTransaction;
use App\Models\Penghuni;
use App\Models\User;
use Database\Seeders\MarketplaceDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplacePurchaseFlowTest extends TestCase
{
    use RefreshDatabase;

    private function createPenghuniTenant(string $email): User
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'occupied']);

        $user = User::factory()->create(['role' => 'pencari', 'email' => $email]);
        Penghuni::factory()->create([
            'user_id' => $user->id,
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'status' => 'active',
        ]);

        return $user;
    }

    public function test_complete_peer_to_peer_purchase_and_review_flow(): void
    {
        // 1. Setup seller dan buyer yang keduanya adalah anak kos aktif (penghuni).
        $seller = $this->createPenghuniTenant('seller@kos.test');
        $buyer = $this->createPenghuniTenant('buyer@kos.test');

        // Buat 1 barang yang dijual oleh seller.
        $item = MarketplaceItem::create([
            'user_id' => $seller->id,
            'kos_id' => $seller->penghunis()->first()->kos_id,
            'title' => 'Meja Belajar Kayu Minimalis',
            'description' => 'Kondisi 95%, dijual karena mau lulus.',
            'price' => 125000,
            'condition' => MarketplaceItem::CONDITION_USED,
            'category' => MarketplaceItem::CATEGORY_FURNITURE,
            'status' => MarketplaceItem::STATUS_AVAILABLE,
        ]);

        $this->assertTrue($item->isAvailable());

        // 2. Buyer tidak boleh beli barang milik sendiri (safety check).
        $this->actingAs($seller)
            ->post(route('tenant.marketplace.buy', $item))
            ->assertRedirect()
            ->assertSessionHas('error');

        // 3. Buyer mengajukan pembelian.
        $this->actingAs($buyer)
            ->post(route('tenant.marketplace.buy', $item))
            ->assertRedirect(route('tenant.marketplace.show', $item))
            ->assertSessionHas('success');

        // Status barang berubah jadi reserved & transaksi pending terbentuk.
        $item->refresh();
        $this->assertSame(MarketplaceItem::STATUS_RESERVED, $item->status);
        $transaction = MarketplaceTransaction::where('item_id', $item->id)->first();
        $this->assertNotNull($transaction);
        $this->assertSame(MarketplaceTransaction::STATUS_PENDING, $transaction->status);
        $this->assertEquals(125000, (float) $transaction->price);
        $this->assertSame($buyer->id, $transaction->buyer_id);
        $this->assertSame($seller->id, $transaction->seller_id);

        // 4. Orang lain tidak bisa menyelesaikan transaksi selain seller.
        $this->actingAs($buyer)
            ->post(route('tenant.marketplace.complete', $transaction))
            ->assertForbidden();

        // 5. Seller menyelesaikan transaksi setelah serah terima / COD.
        $this->actingAs($seller)
            ->post(route('tenant.marketplace.complete', $transaction))
            ->assertRedirect();

        $transaction->refresh();
        $item->refresh();
        $this->assertSame(MarketplaceTransaction::STATUS_COMPLETED, $transaction->status);
        $this->assertSame(MarketplaceItem::STATUS_SOLD, $item->status);

        // 6. Buyer memberikan review & rating setelah transaksi selesai.
        $this->actingAs($buyer)
            ->post(route('tenant.marketplace.review', $transaction), [
                'rating' => 5,
                'comment' => 'Barang mulus sesuai foto, seller ramah dan cepat merespons!',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('marketplace_reviews', [
            'transaction_id' => $transaction->id,
            'reviewer_id' => $buyer->id,
            'rating' => 5,
            'comment' => 'Barang mulus sesuai foto, seller ramah dan cepat merespons!',
        ]);
    }
}
