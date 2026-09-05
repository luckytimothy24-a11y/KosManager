<?php

namespace Tests\Feature;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingOrder;
use App\Models\AdvertisingPackage;
use App\Models\Kos;
use App\Models\User;
use App\Services\AdvertisingAnalytics;
use App\Services\AdvertisingService;
use Database\Seeders\AdvertisingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * GAP audit F2/F3/F6 — integritas ledger revenue advertising:
 * - F2: order paid yang kampanyenya dibatalkan (owner) atau ditolak (moderator)
 *       dimutasikan ke REFUNDED sehingga tidak menggelembungkan revenue.
 * - F3: campaign pihak ketiga yang dibuat moderator otomatis punya order
 *       PENDING (piutang); moderator menandai PAID saat dana diterima.
 * - F6: seeder demo — order partner berstatus pending, AD-DEMO tetap paid,
 *       seeder hanya untuk development.
 */
class AdvertisingLedgerTest extends TestCase
{
    use RefreshDatabase;

    private function setupOwner(): User
    {
        return User::factory()->create(['role' => 'owner']);
    }

    private function marketplacePackage(array $state = [])
    {
        return AdvertisingPackage::factory()->create(array_merge(['placement' => 'marketplace'], $state));
    }

    private function campaign(User $owner, array $overrides = [])
    {
        $package = AdvertisingPackage::factory()->create(['price' => 50000]);

        return AdvertisingCampaign::factory()->create(array_merge([
            'owner_id' => $owner->id,
            'kos_id' => Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active'])->id,
            'package_id' => $package->id,
            'budget' => $package->price,
        ], $overrides));
    }

    // ---------------------------------------------------------------------
    // F3 — Ledger pihak ketiga
    // ---------------------------------------------------------------------

    public function test_moderator_created_third_party_campaign_creates_pending_order(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $package = $this->marketplacePackage(['price' => 75000]);

        $this->actingAs($superAdmin)
            ->post(route('super-admin.advertising.campaigns.store'), [
                'package_id' => $package->id,
                'advertiser_name' => 'Demo Partner WiFi',
                'headline' => 'Iklan Demo Moderator',
                'destination_url' => 'https://example.com/partner',
                'placement' => 'marketplace',
            ])
            ->assertRedirect();

        $campaign = AdvertisingCampaign::whereNotNull('advertiser_name')->whereNull('kos_id')->first();
        $this->assertNotNull($campaign);

        // 1 order PENDING (piutang) — bukan paid otomatis.
        $this->assertDatabaseHas('advertising_orders', [
            'campaign_id' => $campaign->id,
            'owner_id' => $superAdmin->id,
            'status' => AdvertisingOrder::STATUS_PENDING,
            'amount' => 75000,
        ]);
        $this->assertEquals(0, AdvertisingOrder::where('campaign_id', $campaign->id)->where('status', 'paid')->count());
    }

    public function test_admin_can_mark_pending_third_party_order_as_paid(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $package = $this->marketplacePackage(['price' => 100000]);
        $campaign = AdvertisingCampaign::factory()->active()->thirdParty('marketplace')->create([
            'owner_id' => $admin->id,
            'package_id' => $package->id,
            'budget' => 100000,
        ]);
        $order = AdvertisingOrder::factory()->create([
            'campaign_id' => $campaign->id,
            'owner_id' => $admin->id,
            'amount' => 100000,
            'status' => AdvertisingOrder::STATUS_PENDING,
            'paid_at' => null,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.advertising.orders.mark-paid', $order))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('advertising_orders', [
            'id' => $order->id,
            'status' => AdvertisingOrder::STATUS_PAID,
        ]);
        $this->assertNotNull($order->fresh()->paid_at);
    }

    public function test_mark_paid_is_restricted_to_pending_third_party_orders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $owner = $this->setupOwner();
        $package = $this->marketplacePackage();

        // Order pihak ketiga yang SUDAH paid → tidak bisa di-mark paid ulang (403).
        $paidCampaign = AdvertisingCampaign::factory()->active()->thirdParty('marketplace')->create([
            'owner_id' => $superAdmin->id,
            'package_id' => $package->id,
        ]);
        $paidOrder = AdvertisingOrder::factory()->create([
            'campaign_id' => $paidCampaign->id,
            'owner_id' => $superAdmin->id,
            'status' => AdvertisingOrder::STATUS_PAID,
        ]);
        $this->actingAs($admin)
            ->post(route('admin.advertising.orders.mark-paid', $paidOrder))
            ->assertForbidden();

        // Order campaign promosi KOS owner (bukan pihak ketiga) → 403 walaupun pending.
        $kosCampaign = AdvertisingCampaign::factory()->active()->create([
            'owner_id' => $owner->id,
            'kos_id' => Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active'])->id,
            'package_id' => $package->id,
        ]);
        $ownerOrder = AdvertisingOrder::factory()->create([
            'campaign_id' => $kosCampaign->id,
            'owner_id' => $owner->id,
            'status' => AdvertisingOrder::STATUS_PENDING,
            'paid_at' => null,
        ]);
        $this->actingAs($admin)
            ->post(route('admin.advertising.orders.mark-paid', $ownerOrder))
            ->assertForbidden();
        $this->assertDatabaseHas('advertising_orders', ['id' => $ownerOrder->id, 'status' => 'pending']);

        // Owner tidak boleh mark-paid order pihak ketiga.
        $ownerTarget = AdvertisingOrder::factory()->create([
            'campaign_id' => $paidCampaign->id,
            'owner_id' => $superAdmin->id,
            'status' => AdvertisingOrder::STATUS_PENDING,
            'paid_at' => null,
        ]);
        $this->actingAs($owner)
            ->post(route('admin.advertising.orders.mark-paid', $ownerTarget))
            ->assertForbidden();
    }

    // ---------------------------------------------------------------------
    // F2 — Refund/credit untuk paid-then-cancelled / paid-then-rejected
    // ---------------------------------------------------------------------

    public function test_owner_cancel_after_payment_refunds_paid_order(): void
    {
        $owner = $this->setupOwner();
        $campaign = $this->campaign($owner, ['status' => AdvertisingCampaign::STATUS_PENDING_PAYMENT]);
        $service = app(AdvertisingService::class);

        $service->pay($campaign);
        $this->assertDatabaseHas('advertising_orders', ['campaign_id' => $campaign->id, 'status' => 'paid']);

        $service->cancel($campaign->fresh(), $owner);

        $this->assertDatabaseHas('advertising_campaigns', ['id' => $campaign->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('advertising_orders', [
            'campaign_id' => $campaign->id,
            'status' => AdvertisingOrder::STATUS_REFUNDED,
            'refunded_by' => $owner->id,
        ]);
        $this->assertNotNull(AdvertisingOrder::where('campaign_id', $campaign->id)->value('refunded_at'));
        $this->assertEquals(0, AdvertisingOrder::where('campaign_id', $campaign->id)->where('status', 'paid')->count());
    }

    public function test_cancel_before_payment_creates_no_order(): void
    {
        $owner = $this->setupOwner();
        $campaign = $this->campaign($owner, ['status' => AdvertisingCampaign::STATUS_PENDING_PAYMENT]);
        $service = app(AdvertisingService::class);

        $service->cancel($campaign, $owner);

        $this->assertDatabaseHas('advertising_campaigns', ['id' => $campaign->id, 'status' => 'cancelled']);
        $this->assertDatabaseCount('advertising_orders', 0);
    }

    public function test_reject_refunds_paid_order(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $owner = $this->setupOwner();
        $campaign = $this->campaign($owner, ['status' => AdvertisingCampaign::STATUS_PENDING_REVIEW]);
        AdvertisingOrder::factory()->create([
            'campaign_id' => $campaign->id,
            'owner_id' => $owner->id,
            'amount' => 50000,
            'status' => AdvertisingOrder::STATUS_PAID,
        ]);

        $service = app(AdvertisingService::class);
        $service->reject($campaign, 'konten melanggar ketentuan', $superAdmin);

        $this->assertDatabaseHas('advertising_campaigns', ['id' => $campaign->id, 'status' => 'rejected']);
        $this->assertDatabaseHas('advertising_orders', [
            'campaign_id' => $campaign->id,
            'status' => AdvertisingOrder::STATUS_REFUNDED,
            'refunded_by' => $superAdmin->id,
        ]);
        $this->assertEquals(
            'Penolakan kampanye: konten melanggar ketentuan',
            AdvertisingOrder::where('campaign_id', $campaign->id)->value('refund_reason')
        );
    }

    public function test_reject_does_not_touch_order_nonexistent(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $owner = $this->setupOwner();
        $campaign = $this->campaign($owner, ['status' => AdvertisingCampaign::STATUS_PENDING_REVIEW]);

        $service = app(AdvertisingService::class);
        $service->reject($campaign, 'info palsu', $superAdmin);

        $this->assertDatabaseCount('advertising_orders', 0);
        $this->assertDatabaseHas('advertising_campaigns', ['id' => $campaign->id, 'status' => 'rejected']);
    }

    public function test_suspend_keeps_paid_order(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $owner = $this->setupOwner();
        $campaign = $this->campaign($owner, ['status' => AdvertisingCampaign::STATUS_ACTIVE]);
        AdvertisingOrder::factory()->create([
            'campaign_id' => $campaign->id,
            'owner_id' => $owner->id,
            'amount' => 50000,
            'status' => AdvertisingOrder::STATUS_PAID,
        ]);

        $service = app(AdvertisingService::class);
        $service->suspend($campaign, 'lookaside');

        $this->assertDatabaseHas('advertising_campaigns', ['id' => $campaign->id, 'status' => 'suspended']);
        $this->assertDatabaseHas('advertising_orders', ['campaign_id' => $campaign->id, 'status' => 'paid']);
    }

    // ---------------------------------------------------------------------
    // Rekonsiliasi revenue (F2/F3) + F6 seeder
    // ---------------------------------------------------------------------

    public function test_analytics_separates_paid_pending_and_refunded_revenue(): void
    {
        $owner = $this->setupOwner();
        $campaign = $this->campaign($owner);

        AdvertisingOrder::factory()->create(['campaign_id' => $campaign->id, 'owner_id' => $owner->id, 'amount' => 100000, 'status' => 'paid']);
        AdvertisingOrder::factory()->create(['campaign_id' => $campaign->id, 'owner_id' => $owner->id, 'amount' => 25000, 'status' => 'pending']);
        AdvertisingOrder::factory()->create(['campaign_id' => $campaign->id, 'owner_id' => $owner->id, 'amount' => 40000, 'status' => 'refunded']);

        $stats = app(AdvertisingAnalytics::class)->overview();

        $this->assertEquals(100000, (float) $stats['revenue']);
        $this->assertEquals(25000, (float) $stats['pendingRevenue']);
        $this->assertEquals(40000, (float) $stats['refundedRevenue']);
        $this->assertEquals(1, $stats['pendingOrders']);
        $this->assertEquals(1, $stats['refundedOrders']);
    }

    public function test_revenue_report_filters_by_order_status(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $owner = $this->setupOwner();
        $package = AdvertisingPackage::factory()->create(['price' => 50000]);
        $campaign = $this->campaign($owner, ['package_id' => $package->id]);

        $paid = AdvertisingOrder::factory()->create(['campaign_id' => $campaign->id, 'owner_id' => $owner->id, 'amount' => 50000, 'status' => 'paid']);
        $pending = AdvertisingOrder::factory()->create(['campaign_id' => $campaign->id, 'owner_id' => $owner->id, 'amount' => 50000, 'status' => 'pending', 'paid_at' => null]);
        $cancelledCampaign = $this->campaign($owner, ['status' => 'cancelled']);
        $refunded = AdvertisingOrder::factory()->create(['campaign_id' => $cancelledCampaign->id, 'owner_id' => $owner->id, 'amount' => 50000, 'status' => 'refunded']);

        // Default: hanya paid yang tampil di laporan revenue.
        $this->actingAs($superAdmin)
            ->get(route('super-admin.advertising.revenue'))
            ->assertOk()
            ->assertSee($paid->order_number)
            ->assertDontSee($pending->order_number)
            ->assertDontSee($refunded->order_number);

        // Filter status pending menampilkan piutang.
        $this->actingAs($superAdmin)
            ->get(route('super-admin.advertising.revenue', ['status' => 'pending']))
            ->assertOk()
            ->assertSee($pending->order_number)
            ->assertDontSee($paid->order_number);

        // Filter status refunded menampilkan pengembalian.
        $this->actingAs($superAdmin)
            ->get(route('super-admin.advertising.revenue', ['status' => 'refunded']))
            ->assertOk()
            ->assertSee($refunded->order_number)
            ->assertDontSee($paid->order_number);
    }

    public function test_refunded_order_is_excluded_from_owner_spending(): void
    {
        $owner = $this->setupOwner();
        $campaign = $this->campaign($owner, ['status' => AdvertisingCampaign::STATUS_PENDING_REVIEW]);
        AdvertisingOrder::factory()->create([
            'campaign_id' => $campaign->id,
            'owner_id' => $owner->id,
            'amount' => 50000,
            'status' => AdvertisingOrder::STATUS_PAID,
        ]);

        $this->assertEquals(50000, (float) app(AdvertisingAnalytics::class)->ownerOverview($owner->id)['spending']);

        app(AdvertisingService::class)->reject($campaign, 'batch gagal', $owner);

        $this->assertEquals(0, (float) app(AdvertisingAnalytics::class)->ownerOverview($owner->id)['spending']);
    }

    public function test_seed_creates_pending_partner_orders_and_paid_demo_orders(): void
    {
        $owner = User::factory()->create(['email' => 'owner@example.com', 'role' => 'owner']);
        $secondOwner = User::factory()->create(['email' => 'gnamaga@example.com', 'role' => 'owner']);
        User::factory()->create(['role' => 'super_admin']);

        Kos::factory()->create(['name' => 'Kos Melati', 'owner_id' => $owner->id, 'status' => 'active']);
        Kos::factory()->create(['name' => 'Kos Mawar', 'owner_id' => $owner->id, 'status' => 'active']);
        Kos::factory()->create(['name' => 'PT Wahyudin Utama', 'owner_id' => $secondOwner->id, 'status' => 'active']);

        $this->seed(AdvertisingSeeder::class);

        // Kampanye AD-DEMO-* (promosi kos owner) tetap memuat order PAID.
        $this->assertDatabaseHas('advertising_campaigns', ['campaign_number' => 'AD-DEMO-1']);
        foreach (['AD-DEMO-1', 'AD-DEMO-2', 'AD-DEMO-4'] as $num) {
            $campaign = AdvertisingCampaign::where('campaign_number', $num)->first();
            $this->assertEquals('paid', AdvertisingOrder::where('campaign_id', $campaign->id)->first()->status);
        }

        // Kampanye AD-PARTNER-* (pihak ketiga) order-nya PENDING — tidak menambah revenue.
        foreach (['AD-PARTNER-1', 'AD-PARTNER-2', 'AD-PARTNER-3', 'AD-PARTNER-4'] as $num) {
            $campaign = AdvertisingCampaign::where('campaign_number', $num)->first();
            $this->assertNotNull($campaign);
            $this->assertEquals(
                'pending',
                AdvertisingOrder::where('campaign_id', $campaign->id)->first()->status
            );
        }

        $this->assertEquals(4, AdvertisingOrder::where('status', 'pending')->count());

        // Idempotent: seed ulang tidak menggandakan kampanye/order.
        $this->seed(AdvertisingSeeder::class);
        $this->assertEquals(4, AdvertisingOrder::where('status', 'pending')->count());
        $this->assertEquals(3, AdvertisingOrder::where('status', 'paid')->count());
    }
}
