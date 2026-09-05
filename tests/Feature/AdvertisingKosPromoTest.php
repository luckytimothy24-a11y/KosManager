<?php

namespace Tests\Feature;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingPackage;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvertisingKosPromoTest extends TestCase
{
    use RefreshDatabase;

    private function kosPromoCampaign(User $owner, Kos $kos, array $state = []): AdvertisingCampaign
    {
        $package = AdvertisingPackage::factory()->create(['placement' => 'marketplace']);

        return AdvertisingCampaign::factory()->create(array_merge([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
            'is_featured' => false,
            'is_sponsored' => true,
        ], $state));
    }

    public function test_marketplace_renders_labeled_featured_and_sponsored_kos_promotions(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = User::factory()->create(['role' => 'owner']);

        $featuredKos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'name' => 'Kos Melati Promo']);
        Kamar::factory()->create(['kos_id' => $featuredKos->id, 'status' => 'available', 'monthly_price' => 600000]);
        $sponsoredKos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'name' => 'Kos Mawar Promo']);
        Kamar::factory()->create(['kos_id' => $sponsoredKos->id, 'status' => 'available', 'monthly_price' => 500000]);

        $this->kosPromoCampaign($owner, $featuredKos, ['is_featured' => true]);
        $this->kosPromoCampaign($owner, $sponsoredKos);

        $this->actingAs($tenant)
            ->get(route('tenant.kos.index'))
            ->assertOk()
            ->assertSee('Kos Unggulan')
            ->assertSee('Kos Melati Promo')
            ->assertSee('Kos Featured')
            ->assertSee('Kos Mawar Promo')
            ->assertSee('Kos Sponsored');

        foreach (AdvertisingCampaign::whereNotNull('kos_id')->pluck('id') as $campaignId) {
            $this->assertDatabaseHas('advertising_events', [
                'campaign_id' => $campaignId,
                'type' => 'impression',
                'placement' => 'marketplace',
            ]);
        }
    }

    public function test_marketplace_kos_promotions_respect_price_filter(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = User::factory()->create(['role' => 'owner']);

        $expensive = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'name' => 'Kos Premium Promo']);
        Kamar::factory()->create(['kos_id' => $expensive->id, 'status' => 'available', 'monthly_price' => 2000000]);
        $this->kosPromoCampaign($owner, $expensive);

        $cheap = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'name' => 'Kos Hemat']);
        Kamar::factory()->create(['kos_id' => $cheap->id, 'status' => 'available', 'monthly_price' => 500000]);

        $this->actingAs($tenant)
            ->get(route('tenant.kos.index', ['price_max' => 1000000]))
            ->assertOk()
            ->assertSee('Kos Hemat')
            ->assertDontSee('Kos Premium Promo');
    }

    public function test_marketplace_kos_promotions_exclude_non_active_campaigns(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = User::factory()->create(['role' => 'owner']);

        $suspendedKos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'name' => 'Kos NonLive']);
        Kamar::factory()->create(['kos_id' => $suspendedKos->id, 'status' => 'available', 'monthly_price' => 400000]);
        $this->kosPromoCampaign($owner, $suspendedKos, ['status' => AdvertisingCampaign::STATUS_SUSPENDED]);

        $futureKos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'name' => 'Kos Jadwal Depan']);
        Kamar::factory()->create(['kos_id' => $futureKos->id, 'status' => 'available', 'monthly_price' => 500000]);
        $this->kosPromoCampaign($owner, $futureKos, [
            'status' => AdvertisingCampaign::STATUS_APPROVED,
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(5),
        ]);

        // Kedua kos tetap tampil murni organik, TAPI tidak ada promosi kos
        // (section "Kos Unggulan" tidak dirender) karena kampanye tidak live.
        $this->actingAs($tenant)
            ->get(route('tenant.kos.index'))
            ->assertOk()
            ->assertSee('Kos NonLive')
            ->assertSee('Kos Jadwal Depan')
            ->assertDontSee('Kos Unggulan');

        foreach (AdvertisingCampaign::whereNotNull('kos_id')->pluck('id') as $campaignId) {
            $this->assertDatabaseMissing('advertising_events', [
                'campaign_id' => $campaignId,
                'type' => 'impression',
            ]);
        }
    }

    public function test_homepage_kos_promotions_display_on_tenant_dashboard(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = User::factory()->create(['role' => 'owner']);

        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active', 'name' => 'Kos Beranda Promo']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'monthly_price' => 700000]);

        $package = AdvertisingPackage::factory()->create(['placement' => 'homepage']);
        $campaign = AdvertisingCampaign::factory()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
            'is_homepage' => true,
            'is_featured' => false,
            'placement' => 'homepage',
        ]);

        $this->actingAs($tenant)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Promosi Kos')
            ->assertSee('Kos Beranda Promo');

        $this->assertDatabaseHas('advertising_events', [
            'campaign_id' => $campaign->id,
            'type' => 'impression',
            'placement' => 'homepage',
        ]);
    }

    public function test_kos_promotion_click_tracks_and_redirects_to_kos_detail(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = User::factory()->create(['role' => 'owner']);

        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $campaign = $this->kosPromoCampaign($owner, $kos);

        $this->actingAs($tenant)
            ->get(route('tenant.ad.click', ['campaign' => $campaign->id, 'placement' => 'marketplace']))
            ->assertRedirect(route('tenant.kos.show', $kos));

        $this->assertDatabaseHas('advertising_events', [
            'campaign_id' => $campaign->id,
            'type' => 'click',
        ]);
    }
}
