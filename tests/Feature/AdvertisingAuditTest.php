<?php

namespace Tests\Feature;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingOrder;
use App\Models\AdvertisingPackage;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use App\Services\AdvertisingAnalytics;
use App\Services\AdvertisingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvertisingAuditTest extends TestCase
{
    use RefreshDatabase;

    private function setupOwner(): User
    {
        return User::factory()->create(['role' => 'owner']);
    }

    private function campaign(User $owner, Kos $kos, string $status = 'active')
    {
        $package = AdvertisingPackage::factory()->create(['price' => 50000]);

        return AdvertisingCampaign::factory()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
            'status' => $status,
            'budget' => $package->price,
        ]);
    }

    public function test_owner_cannot_view_another_owners_campaign_detail(): void
    {
        $ownerA = $this->setupOwner();
        $ownerB = $this->setupOwner();
        $kosB = Kos::factory()->create(['owner_id' => $ownerB->id, 'status' => 'active']);
        $camp = $this->campaign($ownerB, $kosB);

        $this->actingAs($ownerA)->get(route('owner.advertising.show', $camp))->assertForbidden();
        $this->actingAs($ownerA)->post(route('owner.advertising.cancel', $camp))->assertForbidden();
        $this->actingAs($ownerA)->post(route('owner.advertising.pay', $camp))->assertForbidden();
    }

    public function test_admin_cannot_review_another_owners_kos_campaign(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = $this->setupOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $camp = AdvertisingCampaign::factory()->pendingReview()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => AdvertisingPackage::factory()->create()->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.advertising.index'))
            ->assertOk()
            ->assertDontSee($camp->campaign_number);

        $this->actingAs($admin)->post(route('admin.advertising.approve', $camp))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.advertising.reject', $camp), ['reason' => 'x'])->assertForbidden();
        $this->actingAs($admin)->post(route('admin.advertising.suspend', $camp), ['reason' => 'x'])->assertForbidden();
    }

    public function test_admin_cannot_bypass_scope_via_campaign_id_or_kos_id(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = $this->setupOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $camp = AdvertisingCampaign::factory()->pendingReview()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => AdvertisingPackage::factory()->create()->id,
        ]);

        // Coba akses langsung ke show/action dengan ID campaign kos lain.
        $this->actingAs($admin)->get(route('admin.advertising.show', $camp))->assertForbidden();

        // Game via query param / hidden input juga harus gagal di sisi policy.
        $this->actingAs($admin)
            ->post(route('admin.advertising.approve', $camp).'?kos_id='.$kos->id)
            ->assertForbidden();
    }

    public function test_owner_cannot_access_super_admin_advertising_routes(): void
    {
        $owner = $this->setupOwner();
        $package = AdvertisingPackage::factory()->create();

        $this->actingAs($owner)->get(route('super-admin.advertising.packages.index'))->assertForbidden();
        $this->actingAs($owner)->post(route('super-admin.advertising.packages.store'), [])->assertForbidden();
        $this->actingAs($owner)->put(route('super-admin.advertising.packages.update', $package), [])->assertForbidden();
        $this->actingAs($owner)->delete(route('super-admin.advertising.packages.destroy', $package))->assertForbidden();
        $this->actingAs($owner)->get(route('super-admin.advertising.campaigns.index'))->assertForbidden();
        $this->actingAs($owner)->get(route('super-admin.advertising.revenue'))->assertForbidden();
        $this->actingAs($owner)->get(route('super-admin.advertising.revenue.export'))->assertForbidden();
    }

    public function test_admin_cannot_access_super_admin_package_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $package = AdvertisingPackage::factory()->create();

        $this->actingAs($admin)->get(route('super-admin.advertising.packages.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('super-admin.advertising.revenue'))->assertForbidden();
        $this->actingAs($admin)->get(route('super-admin.advertising.campaigns.index'))->assertForbidden();
    }

    public function test_marketplace_sponsored_kos_respects_price_filter(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = $this->setupOwner();
        $expensive = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        Kamar::factory()->create(['kos_id' => $expensive->id, 'status' => 'available', 'monthly_price' => 1500000]);
        $this->campaign($owner, $expensive, 'active');

        $cheap = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        Kamar::factory()->create(['kos_id' => $cheap->id, 'status' => 'available', 'monthly_price' => 500000]);

        $this->actingAs($tenant)
            ->get(route('tenant.kos.index', ['price_max' => 1000000]))
            ->assertOk()
            ->assertSee($cheap->name)
            ->assertDontSee($expensive->name);
    }

    public function test_expired_campaign_click_is_not_tracked(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = $this->setupOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $camp = AdvertisingCampaign::factory()->expired()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => AdvertisingPackage::factory()->create()->id,
        ]);

        $this->actingAs($tenant)
            ->get(route('tenant.ad.click', ['campaign' => $camp->id, 'placement' => 'marketplace']))
            ->assertRedirect(route('tenant.kos.show', $kos));

        $this->assertDatabaseMissing('advertising_events', ['campaign_id' => $camp->id, 'type' => 'click']);
        $this->assertFalse(session()->has('ad_click'));
    }

    public function test_suspended_and_rejected_campaigns_not_clickable_as_active(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = $this->setupOwner();

        foreach (['suspended', 'rejected'] as $status) {
            $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
            $camp = AdvertisingCampaign::factory()->create([
                'owner_id' => $owner->id,
                'kos_id' => $kos->id,
                'package_id' => AdvertisingPackage::factory()->create()->id,
                'status' => $status,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(5),
            ]);

            $this->actingAs($tenant)
                ->get(route('tenant.ad.click', ['campaign' => $camp->id]));
            $this->assertDatabaseMissing('advertising_events', ['campaign_id' => $camp->id, 'type' => 'click']);
        }
    }

    public function test_organic_booking_does_not_produce_conversion_event(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = $this->setupOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $camp = $this->campaign($owner, $kos, 'active');

        // Booking organik TANPA klik iklan.
        $this->actingAs($tenant)->post(route('tenant.booking.store'), [
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'rental_type' => 'daily',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('advertising_events', ['campaign_id' => $camp->id, 'type' => 'conversion']);
    }

    public function test_analytics_handles_zero_impressions_and_clicks_without_errors(): void
    {
        $analytics = app(AdvertisingAnalytics::class);
        $overview = $analytics->overview();
        $this->assertEquals(0, $overview['impressions']);
        $this->assertEquals(0, $overview['clicks']);
        $this->assertEquals(0.0, $overview['ctr']);
        $this->assertEquals(0.0, $overview['conversionRate']);
        $this->assertEquals(0, $overview['revenue']);
    }

    public function test_revenue_counts_each_paid_order_once(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $owner = $this->setupOwner();
        $package = AdvertisingPackage::factory()->create(['price' => 100000]);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);

        $c1 = $this->campaign($owner, $kos, 'active');
        AdvertisingOrder::factory()->create(['campaign_id' => $c1->id, 'owner_id' => $owner->id, 'amount' => 100000, 'status' => 'paid']);
        $c2 = $this->campaign($owner, $kos, 'active');
        AdvertisingOrder::factory()->create(['campaign_id' => $c2->id, 'owner_id' => $owner->id, 'amount' => 100000, 'status' => 'paid']);

        $analytics = app(AdvertisingAnalytics::class);
        $this->assertEquals(200000, (float) $analytics->overview()['revenue']);

        // Revenue halaman hanya menampilkan order paid.
        $this->actingAs($superAdmin)
            ->get(route('super-admin.advertising.revenue'))
            ->assertOk()
            ->assertSee('Rp 100.000');
    }

    public function test_csv_export_requires_super_admin_and_returns_content(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $owner = $this->setupOwner();
        $package = AdvertisingPackage::factory()->create(['price' => 50000]);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $camp = $this->campaign($owner, $kos, 'active');
        AdvertisingOrder::factory()->create(['campaign_id' => $camp->id, 'owner_id' => $owner->id, 'amount' => 50000, 'status' => 'paid']);

        $ownerUser = $this->setupOwner();
        $this->actingAs($ownerUser)->get(route('super-admin.advertising.revenue.export'))->assertForbidden();

        $response = $this->actingAs($superAdmin)->get(route('super-admin.advertising.revenue.export'));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Rp 50.000', $response->getContent());
    }

    public function test_marketplace_only_promotes_partner_campaign_in_its_time_window(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = $this->setupOwner();
        $package = AdvertisingPackage::factory()->create(['price' => 50000, 'placement' => 'marketplace']);

        // Campaign pihak ketiga belum masuk window (approved, future) → tidak dipromosikan.
        AdvertisingCampaign::factory()->thirdParty('marketplace')->create([
            'owner_id' => $owner->id,
            'package_id' => $package->id,
            'status' => 'approved',
            'starts_at' => now()->addDays(1),
            'ends_at' => now()->addDays(3),
        ]);

        $this->actingAs($tenant)
            ->get(route('tenant.kos.index'))
            ->assertOk();
        // Tidak ada impression untuk campaign yang belum masuk window.
        $camp = AdvertisingCampaign::whereNotNull('advertiser_name')->where('kos_id', null)->first();
        $this->assertDatabaseMissing('advertising_events', ['campaign_id' => $camp->id, 'type' => 'impression']);
    }

    public function test_tenant_dashboard_and_marketplace_render_partner_promoted_ui(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = $this->setupOwner();

        $package = AdvertisingPackage::factory()->create(['price' => 100000, 'placement' => 'homepage']);
        $homepageAd = AdvertisingCampaign::factory()->active()->thirdParty('homepage')->create([
            'owner_id' => $owner->id,
            'package_id' => $package->id,
            'headline' => 'Laundry Antar Jemput di Jantung Kos',
        ]);
        $marketplaceAd = AdvertisingCampaign::factory()->active()->thirdParty('marketplace')->create([
            'owner_id' => $owner->id,
            'package_id' => $package->id,
            'advertiser_name' => 'Demo Partner WiFi',
            'headline' => 'WiFi Cepat Tanpa Ribet untuk Anak Kos',
        ]);

        // Dashboard tenant menampilkan iklan advertiser pihak ketiga (homepage).
        $this->actingAs($tenant)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Laundry Antar Jemput di Jantung Kos')
            ->assertSee('Promoted Partner');

        // Marketplace menampilkan iklan pihak ketiga (placement marketplace) — bukan kos.
        $this->actingAs($tenant)
            ->get(route('tenant.kos.index'))
            ->assertOk()
            ->assertSee('WiFi Cepat Tanpa Ribet untuk Anak Kos');

        // Impression tracking (di-dedup) terpakai untuk kedua placement.
        $this->assertDatabaseHas('advertising_events', [
            'campaign_id' => $homepageAd->id,
            'type' => 'impression',
            'placement' => 'homepage',
        ]);
        $this->assertDatabaseHas('advertising_events', [
            'campaign_id' => $marketplaceAd->id,
            'type' => 'impression',
            'placement' => 'marketplace',
        ]);
    }

    public function test_owner_create_page_renders_with_live_preview(): void
    {
        $owner = $this->setupOwner();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'monthly_price' => 750000]);
        $package = AdvertisingPackage::factory()->create(['price' => 60000, 'is_featured' => true]);

        $this->actingAs($owner)
            ->get(route('owner.advertising.create'))
            ->assertOk()
            ->assertSee($kos->name)
            ->assertSee($package->name)
            ->assertSee('Preview Iklan');
    }

    public function test_fair_rotation_gives_every_advertiser_a_turn_before_repeating(): void
    {
        $service = app(AdvertisingService::class);
        $ownerA = $this->setupOwner();
        $ownerB = $this->setupOwner();
        $ownerC = $this->setupOwner();
        $package = AdvertisingPackage::factory()->create(['price' => 60000, 'placement' => 'marketplace']);

        $a1 = AdvertisingCampaign::factory()->active()->thirdParty('marketplace')->create(['owner_id' => $ownerA->id, 'package_id' => $package->id]);
        $a2 = AdvertisingCampaign::factory()->active()->thirdParty('marketplace')->create(['owner_id' => $ownerA->id, 'package_id' => $package->id]);
        $b1 = AdvertisingCampaign::factory()->active()->thirdParty('marketplace')->create(['owner_id' => $ownerB->id, 'package_id' => $package->id]);
        $c1 = AdvertisingCampaign::factory()->active()->thirdParty('marketplace')->create(['owner_id' => $ownerC->id, 'package_id' => $package->id]);

        // Deterministik untuk seed yang sama.
        $r1 = $service->fairRotate(collect([$a1, $a2, $b1, $c1]), 'owner_id', 20260905)->pluck('id')->all();
        $r2 = $service->fairRotate(collect([$a1, $a2, $b1, $c1]), 'owner_id', 20260905)->pluck('id')->all();
        $this->assertSame($r1, $r2);

        // Fair rotation: setiap advertiser mendapat giliran sebelum ada yang berulang.
        $ordered = $service->fairRotate(collect([$a1, $a2, $b1, $c1]), 'owner_id', 20260905)->values();
        $owners = $ordered->pluck('owner_id')->map(fn ($id) => (int) $id)->all();
        $seen = [];
        foreach ($owners as $i => $ownerId) {
            if (in_array($ownerId, $seen, true)) {
                $this->assertGreaterThanOrEqual(3, $i, 'Setiap advertiser harus mendapat giliran sebelum ada yang berulang.');
                break;
            }
            $seen[] = $ownerId;
        }

        // partnerAds hanya mengembalikan campaign pihak ketiga dalam placement.
        $ads = $service->partnerAds('marketplace', 10);
        $this->assertCount(4, $ads);
        $this->assertContains($a1->id, $ads->pluck('id')->all());
        $this->assertContains($c1->id, $ads->pluck('id')->all());
    }
}
