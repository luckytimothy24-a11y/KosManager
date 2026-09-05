<?php

namespace Tests\Feature;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingEvent;
use App\Models\AdvertisingPackage;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use App\Services\AdvertisingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvertisingThirdPartyTest extends TestCase
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

    public function test_owner_can_create_third_party_campaign_without_kos(): void
    {
        $owner = $this->setupOwner();
        $package = $this->marketplacePackage();

        $this->actingAs($owner)
            ->post(route('owner.advertising.store'), [
                'ad_type' => 'partner',
                'package_id' => $package->id,
                'advertiser_name' => 'Demo Partner WiFi',
                'headline' => 'WiFi Cepat untuk Anak Kos',
                'advertiser_description' => 'Paket internet khusus penghuni kos.',
                'cta_label' => 'Cek Paket',
                'destination_url' => 'https://example.com/wifi',
                'placement' => 'marketplace',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('advertising_campaigns', [
            'owner_id' => $owner->id,
            'kos_id' => null,
            'package_id' => $package->id,
            'advertiser_name' => 'Demo Partner WiFi',
            'headline' => 'WiFi Cepat untuk Anak Kos',
            'destination_url' => 'https://example.com/wifi',
            'status' => AdvertisingCampaign::STATUS_PENDING_PAYMENT,
            'budget' => $package->price,
        ]);
    }

    public function test_third_party_campaign_requires_partner_fields(): void
    {
        $owner = $this->setupOwner();
        $package = $this->marketplacePackage();

        // Destination URL wajib untuk iklan pihak ketiga.
        $this->actingAs($owner)
            ->post(route('owner.advertising.store'), [
                'ad_type' => 'partner',
                'package_id' => $package->id,
                'advertiser_name' => 'Demo Partner',
                'headline' => 'Headline',
                'destination_url' => 'not-a-url',
            ])
            ->assertSessionHasErrors(['destination_url']);

        $this->assertDatabaseCount('advertising_campaigns', 0);
    }

    public function test_partner_ads_only_returns_matching_placement(): void
    {
        $owner = $this->setupOwner();
        $package = $this->marketplacePackage();

        AdvertisingCampaign::factory()->active()->thirdParty('marketplace')->create(['owner_id' => $owner->id, 'package_id' => $package->id]);
        AdvertisingCampaign::factory()->active()->thirdParty('homepage')->create(['owner_id' => $owner->id, 'package_id' => $package->id]);

        $service = app(AdvertisingService::class);
        $marketplace = $service->partnerAds('marketplace', 10);
        $this->assertCount(1, $marketplace);
        $this->assertEquals('marketplace', $marketplace->first()->placement);

        $homepage = $service->partnerAds('homepage', 10);
        $this->assertCount(1, $homepage);
        $this->assertEquals('homepage', $homepage->first()->placement);
    }

    public function test_partner_ads_display_on_homepage_marketplace_and_detail(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = $this->setupOwner();
        $package = $this->marketplacePackage();

        $home = AdvertisingCampaign::factory()->active()->thirdParty('homepage')->create([
            'owner_id' => $owner->id,
            'package_id' => $package->id,
            'advertiser_name' => 'Demo Home Laundry',
            'headline' => 'Laundry Antar Jemput Khusus Home',
        ]);
        $market = AdvertisingCampaign::factory()->active()->thirdParty('marketplace')->create([
            'owner_id' => $owner->id,
            'package_id' => $package->id,
            'advertiser_name' => 'Demo Market WiFi',
            'headline' => 'Market WiFi Super Cepat',
        ]);
        $detailAd = AdvertisingCampaign::factory()->active()->thirdParty('detail')->create([
            'owner_id' => $owner->id,
            'package_id' => $package->id,
            'advertiser_name' => 'Demo Detail Furniture',
            'headline' => 'Furnitur Hemat untuk Kamar Kos',
        ]);

        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'monthly_price' => 800000]);

        // Homepage
        $this->actingAs($tenant)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Laundry Antar Jemput Khusus Home')
            ->assertDontSee('Market WiFi Super Cepat');

        // Marketplace
        $this->actingAs($tenant)
            ->get(route('tenant.kos.index'))
            ->assertOk()
            ->assertSee('Market WiFi Super Cepat')
            ->assertDontSee('Laundry Antar Jemput Khusus Home');

        // Detail kos
        $this->actingAs($tenant)
            ->get(route('tenant.kos.show', $kos))
            ->assertOk()
            ->assertSee('Furnitur Hemat untuk Kamar Kos');

        // Impression di-dedup terpisah per placement.
        $this->assertDatabaseHas('advertising_events', ['campaign_id' => $home->id, 'type' => 'impression', 'placement' => 'homepage']);
        $this->assertDatabaseHas('advertising_events', ['campaign_id' => $market->id, 'type' => 'impression', 'placement' => 'marketplace']);
        $this->assertDatabaseHas('advertising_events', ['campaign_id' => $detailAd->id, 'type' => 'impression', 'placement' => 'detail']);
    }

    public function test_third_party_click_redirects_to_destination_and_tracks(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = $this->setupOwner();
        $package = $this->marketplacePackage();
        $campaign = AdvertisingCampaign::factory()->active()->thirdParty('marketplace')->create([
            'owner_id' => $owner->id,
            'package_id' => $package->id,
            'destination_url' => 'https://example.com/landing',
        ]);

        $this->actingAs($tenant)
            ->get(route('tenant.ad.click', ['campaign' => $campaign->id, 'placement' => 'marketplace']))
            ->assertRedirect('https://example.com/landing');

        $this->assertDatabaseHas('advertising_events', [
            'campaign_id' => $campaign->id,
            'type' => 'click',
            'user_id' => $tenant->id,
        ]);

        // Iklan pihak ketiga TIDAK memakai session ad_click (tidak ada atribusi booking).
        $this->assertFalse(session()->has('ad_click'));
    }

    public function test_duplicate_click_in_same_session_is_deduplicated(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = $this->setupOwner();
        $package = $this->marketplacePackage();
        $campaign = AdvertisingCampaign::factory()->active()->thirdParty('marketplace')->create([
            'owner_id' => $owner->id,
            'package_id' => $package->id,
            'destination_url' => 'https://example.com/landing',
        ]);

        $service = app(AdvertisingService::class);

        // Klik pertama tercatat, klik kedua sesi sama dalam jendela dedup TIDAK.
        $service->trackEvent($campaign->id, 'click', $tenant->id, 'sess-click', 'marketplace', true);
        $service->trackEvent($campaign->id, 'click', $tenant->id, 'sess-click', 'marketplace', true);

        $this->assertDatabaseHas('advertising_events', ['campaign_id' => $campaign->id, 'type' => 'click']);
        $this->assertEquals(1, AdvertisingEvent::where('campaign_id', $campaign->id)->where('type', 'click')->count());
    }

    public function test_third_party_click_does_not_attribute_conversion_on_booking(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = $this->setupOwner();
        $package = $this->marketplacePackage();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $campaign = AdvertisingCampaign::factory()->active()->thirdParty('marketplace')->create([
            'owner_id' => $owner->id,
            'package_id' => $package->id,
            'destination_url' => 'https://example.com/landing',
        ]);

        $this->actingAs($tenant)->get(route('tenant.ad.click', ['campaign' => $campaign->id, 'placement' => 'marketplace']));

        $this->actingAs($tenant)
            ->post(route('tenant.booking.store'), [
                'kos_id' => $kos->id,
                'kamar_id' => $kamar->id,
                'start_date' => now()->addDays(1)->format('Y-m-d'),
                'end_date' => now()->addDays(3)->format('Y-m-d'),
                'rental_type' => 'daily',
            ])
            ->assertSessionHasNoErrors();

        // Tidak ada attribution conversion dari iklan pihak ketiga.
        $this->assertDatabaseMissing('advertising_events', ['campaign_id' => $campaign->id, 'type' => 'conversion']);
    }

    public function test_third_party_click_falls_back_when_destination_invalid(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = $this->setupOwner();
        $package = $this->marketplacePackage();
        $campaign = AdvertisingCampaign::factory()->active()->thirdParty('marketplace')->create([
            'owner_id' => $owner->id,
            'package_id' => $package->id,
            'destination_url' => 'javascript:alert(1)',
        ]);

        $this->actingAs($tenant)
            ->get(route('tenant.ad.click', ['campaign' => $campaign->id]))
            ->assertRedirect(route('tenant.kos.index'));
    }

    public function test_super_admin_can_review_third_party_campaign(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $owner = $this->setupOwner();
        $package = $this->marketplacePackage();
        $campaign = AdvertisingCampaign::factory()->pendingReview()->thirdParty('marketplace')->create([
            'owner_id' => $owner->id,
            'package_id' => $package->id,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('super-admin.advertising.campaigns.approve', $campaign))
            ->assertRedirect();

        $this->assertDatabaseHas('advertising_campaigns', [
            'id' => $campaign->id,
            'status' => AdvertisingCampaign::STATUS_ACTIVE,
            'approved_by' => $superAdmin->id,
        ]);
    }

    public function test_admin_can_review_third_party_campaign_globally(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = $this->setupOwner();
        $package = $this->marketplacePackage();
        $campaign = AdvertisingCampaign::factory()->pendingReview()->thirdParty('marketplace')->create([
            'owner_id' => $owner->id,
            'package_id' => $package->id,
        ]);

        // Admin tanpa kos assigned tetap dapat meninjau iklan pihak ketiga (global).
        $this->actingAs($admin)
            ->get(route('admin.advertising.index'))
            ->assertOk()
            ->assertSee($campaign->campaign_number);

        $this->actingAs($admin)
            ->post(route('admin.advertising.approve', $campaign))
            ->assertRedirect();

        $this->assertDatabaseHas('advertising_campaigns', ['id' => $campaign->id, 'status' => 'active']);
    }

    public function test_owner_cannot_modify_another_owners_third_party_campaign(): void
    {
        $ownerA = $this->setupOwner();
        $ownerB = $this->setupOwner();
        $package = $this->marketplacePackage();
        $campaign = AdvertisingCampaign::factory()->thirdParty('marketplace')->create([
            'owner_id' => $ownerB->id,
            'package_id' => $package->id,
            'status' => AdvertisingCampaign::STATUS_PENDING_PAYMENT,
        ]);

        $this->actingAs($ownerA)
            ->post(route('owner.advertising.pay', $campaign))
            ->assertForbidden();
    }

    public function test_marketplace_search_sort_filter_pagination_unchanged_by_ads(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = $this->setupOwner();

        $cheap = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'name' => 'Kos Kampus']);
        Kamar::factory()->create(['kos_id' => $cheap->id, 'status' => 'available', 'monthly_price' => 500000]);
        $expensive = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'name' => 'Kos Premium']);
        Kamar::factory()->create(['kos_id' => $expensive->id, 'status' => 'available', 'monthly_price' => 2000000]);

        // Iklan pihak ketiga hadir tapi tidak menyentuh listing organik.
        $package = $this->marketplacePackage();
        AdvertisingCampaign::factory()->active()->thirdParty('marketplace')->create([
            'owner_id' => $owner->id,
            'package_id' => $package->id,
            'advertiser_name' => 'Demo Promo',
        ]);

        // Search tetap bekerja.
        $this->actingAs($tenant)
            ->get(route('tenant.kos.index', ['q' => 'Kampus']))
            ->assertOk()
            ->assertSee('Kos Kampus')
            ->assertDontSee('Kos Premium');

        // Filter harga tetap bekerja.
        $this->actingAs($tenant)
            ->get(route('tenant.kos.index', ['price_max' => 1000000]))
            ->assertOk()
            ->assertSee('Kos Kampus')
            ->assertDontSee('Kos Premium');

        // Sorting harga terendah tidak dipengaruhi iklan.
        $this->actingAs($tenant)
            ->get(route('tenant.kos.index', ['sort' => 'harga_terendah']))
            ->assertOk()
            ->assertSee('Kos Kampus');
    }

    public function test_impressions_for_third_party_are_deduplicated(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = $this->setupOwner();
        $package = $this->marketplacePackage();
        $campaign = AdvertisingCampaign::factory()->active()->thirdParty('marketplace')->create([
            'owner_id' => $owner->id,
            'package_id' => $package->id,
        ]);

        $service = app(AdvertisingService::class);
        $service->trackEvent($campaign->id, 'impression', $tenant->id, 'sess-1', 'marketplace', true);
        $service->trackEvent($campaign->id, 'impression', $tenant->id, 'sess-1', 'marketplace', true);

        $this->assertEquals(1, AdvertisingEvent::where('campaign_id', $campaign->id)->where('type', 'impression')->count());
    }
}
