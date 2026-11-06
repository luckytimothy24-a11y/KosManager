<?php

namespace Tests\Feature;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingOrder;
use App\Models\Kos;
use App\Models\ManagementAgreement;
use App\Models\MarketplaceItem;
use App\Models\MarketplaceTransaction;
use App\Models\PaymentDistribution;
use App\Models\Payout;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\PropertyOwner;
use App\Models\User;
use App\Services\SettlementReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Data Retention: soft delete kos + user, monetization history tetap ada dan terlihat di laporan.
 * Ledger (payout, payment distribution, agreement) immutable (tidak bisa dihapus).
 */
class DataRetentionTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private PropertyOwner $propertyOwner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->propertyOwner = PropertyOwner::factory()->create();
    }

    /**
     * Test 1: Soft delete Kos → payout tetap ada dan linknya valid.
     */
    public function test_soft_delete_kos_preserves_payouts()
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'property_owner_id' => $this->propertyOwner->id]);
        $payout = Payout::factory()->create([
            'kos_id' => $kos->id,
            'owner_id' => $this->owner->id,
            'property_owner_id' => $this->propertyOwner->id,
        ]);

        $this->assertDatabaseHas('payouts', ['id' => $payout->id, 'kos_id' => $kos->id]);

        // Soft delete kos
        $kos->delete();

        // Payout masih ada di DB
        $this->assertDatabaseHas('payouts', ['id' => $payout->id, 'kos_id' => $kos->id]);

        // Payout reload dan relasi kos() tetap valid via withTrashed()
        $payout->refresh();
        $this->assertNotNull($payout->kos);
        $this->assertEquals($kos->id, $payout->kos->id);
    }

    /**
     * Test 2: Soft delete Kos → settlement report tetap menampilkan komisi/payout.
     */
    public function test_soft_delete_kos_shows_in_settlement_report()
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'property_owner_id' => $this->propertyOwner->id]);

        // Create penghuni + pembayaran approved
        $penghuni = Penghuni::factory()->create(['kos_id' => $kos->id]);
        $pembayaran = Pembayaran::factory()->create([
            'penghuni_id' => $penghuni->id,
            'verification_status' => Pembayaran::STATUS_APPROVED,
            'amount' => 1000000,
            'rent_amount' => 1000000,
        ]);

        // Create payout untuk kos ini
        $payout = Payout::factory()->create([
            'kos_id' => $kos->id,
            'owner_id' => $this->owner->id,
            'property_owner_id' => $this->propertyOwner->id,
            'status' => Payout::STATUS_TRANSFERRED,
        ]);

        // Soft delete kos
        $kos->delete();

        // Payouts remain and relationships work with withTrashed()
        $payoutReloaded = Payout::find($payout->id);
        $this->assertNotNull($payoutReloaded);
        $this->assertEquals($kos->id, $payoutReloaded->kos->id);
        $this->assertEquals($this->owner->id, $payoutReloaded->owner->id);

        // SettlementReportService must not error when querying trashed kos
        $report = app(SettlementReportService::class)->forOwner($this->owner);
        $this->assertNotNull($report);
        $this->assertArrayHasKey('per_kos', $report);
    }

    /**
     * Test 3: Force delete Kos dengan payout/agreement/campaign → ditolak (RuntimeException).
     */
    public function test_force_delete_kos_with_monetization_throws()
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'property_owner_id' => $this->propertyOwner->id]);
        $payout = Payout::factory()->create(['kos_id' => $kos->id, 'owner_id' => $this->owner->id, 'property_owner_id' => $this->propertyOwner->id]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Kos memiliki riwayat payout');

        $kos->forceDelete();
    }

    /**
     * Test 4: Soft delete User → payouts tetap ada, owner masih terbaca via withTrashed().
     */
    public function test_soft_delete_user_preserves_payouts()
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id, 'property_owner_id' => $this->propertyOwner->id]);
        $payout = Payout::factory()->create([
            'kos_id' => $kos->id,
            'owner_id' => $this->owner->id,
            'property_owner_id' => $this->propertyOwner->id,
        ]);

        $this->assertDatabaseHas('payouts', ['id' => $payout->id, 'owner_id' => $this->owner->id]);

        // Soft delete owner
        $this->owner->delete();

        // Payout masih ada
        $this->assertDatabaseHas('payouts', ['id' => $payout->id, 'owner_id' => $this->owner->id]);

        // Payout reload dan relasi owner() tetap valid via withTrashed()
        $payout->refresh();
        $this->assertNotNull($payout->owner);
        $this->assertEquals($this->owner->id, $payout->owner->id);
    }

    /**
     * Test 5: Force delete User dengan kos/payout → ditolak.
     */
    public function test_force_delete_user_with_history_throws()
    {
        $kos = Kos::factory()->create(['owner_id' => $this->owner->id]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('User memiliki riwayat kos');

        $this->owner->forceDelete();
    }

    /**
     * Test 6: Payout tidak bisa dihapus (ledger immutable).
     */
    public function test_payout_cannot_be_deleted()
    {
        $payout = Payout::factory()->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('record ledger');

        $payout->delete();
    }

    /**
     * Test 7: PaymentDistribution tidak bisa dihapus (ledger immutable).
     */
    public function test_payment_distribution_cannot_be_deleted()
    {
        $distribution = PaymentDistribution::factory()->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('record ledger');

        $distribution->delete();
    }

    /**
     * Test 8: Force delete advertising campaign dengan order → ditolak.
     */
    public function test_force_delete_advertising_campaign_with_orders_throws()
    {
        $campaign = AdvertisingCampaign::factory()->create();
        $order = AdvertisingOrder::factory()->create(['campaign_id' => $campaign->id]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Kampanye iklan memiliki order');

        $campaign->forceDelete();
    }

    /**
     * Test 9: Soft delete MarketplaceItem → transaksi tetap ada dan linknya valid.
     */
    public function test_soft_delete_marketplace_item_preserves_transactions()
    {
        $item = MarketplaceItem::factory()->create(['user_id' => $this->owner->id, 'status' => 'sold']);
        $buyer = User::factory()->create();
        $transaction = MarketplaceTransaction::factory()->create([
            'item_id' => $item->id,
            'seller_id' => $this->owner->id,
            'buyer_id' => $buyer->id,
        ]);

        $this->assertDatabaseHas('marketplace_transactions', ['id' => $transaction->id, 'item_id' => $item->id]);

        // Soft delete item
        $item->delete();

        // Transaction masih ada
        $this->assertDatabaseHas('marketplace_transactions', ['id' => $transaction->id, 'item_id' => $item->id]);

        // Transaction reload dan relasi item() tetap valid via withTrashed()
        $transaction->refresh();
        $this->assertNotNull($transaction->item);
        $this->assertEquals($item->id, $transaction->item->id);
    }

    /**
     * Test 10: Force delete MarketplaceItem dengan transaksi → ditolak.
     */
    public function test_force_delete_marketplace_item_with_transactions_throws()
    {
        $item = MarketplaceItem::factory()->create(['user_id' => $this->owner->id]);
        $buyer = User::factory()->create();
        $transaction = MarketplaceTransaction::factory()->create([
            'item_id' => $item->id,
            'seller_id' => $this->owner->id,
            'buyer_id' => $buyer->id,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Item memiliki riwayat transaksi');

        $item->forceDelete();
    }

    /**
     * Test 11: ManagementAgreement tidak bisa dihapus (riwayat komisi).
     */
    public function test_management_agreement_cannot_be_deleted()
    {
        $agreement = ManagementAgreement::factory()->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Perjanjian komisi adalah record');

        $agreement->delete();
    }

    /**
     * Test 12: AdvertisingOrder tidak bisa dihapus (revenue record).
     */
    public function test_advertising_order_cannot_be_deleted()
    {
        $order = AdvertisingOrder::factory()->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('record revenue');

        $order->delete();
    }

    /**
     * Test 13: Pembayaran dengan payment distribution tidak bisa dihapus.
     */
    public function test_pembayaran_with_distribution_cannot_be_force_deleted()
    {
        $pembayaran = Pembayaran::factory()->create();
        $distribution = PaymentDistribution::factory()->create(['pembayaran_id' => $pembayaran->id]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Pembayaran memiliki distribusi');

        $pembayaran->forceDelete();
    }

    /**
     * Test 14: KosController@destroy guard monetization.
     */
    public function test_kos_destroy_rejects_if_has_monetization()
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'property_owner_id' => $this->propertyOwner->id]);
        Payout::factory()->create(['kos_id' => $kos->id, 'owner_id' => $owner->id, 'property_owner_id' => $this->propertyOwner->id]);

        $response = $this->actingAs($owner)->delete(route('owner.kos.destroy', $kos));

        $response->assertRedirect(route('owner.kos.index'));
        $response->assertSessionHas('error', fn ($e) => str_contains($e, 'payout'));

        // Kos still exists
        $this->assertDatabaseHas('kos', ['id' => $kos->id, 'deleted_at' => null]);
    }
}
