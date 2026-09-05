<?php

namespace Tests\Feature;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingPackage;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvertisingModeratorThirdPartyTest extends TestCase
{
    use RefreshDatabase;

    private function marketplacePackage(array $state = [])
    {
        return AdvertisingPackage::factory()->create(array_merge(['placement' => 'marketplace'], $state));
    }

    private function thirdPartyPayload(array $overrides = []): array
    {
        return array_merge([
            'package_id' => $this->marketplacePackage()->id,
            'advertiser_name' => 'Demo Moderator Ads',
            'headline' => 'Iklan Demo dari Moderator',
            'advertiser_description' => 'Deskripsi iklan pihak ketiga.',
            'cta_label' => 'Lihat Penawaran',
            'destination_url' => 'https://example.com/moderator',
            'placement' => 'marketplace',
        ], $overrides);
    }

    public function test_super_admin_can_create_third_party_campaign(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)
            ->post(route('super-admin.advertising.campaigns.store'), $this->thirdPartyPayload())
            ->assertRedirect();

        $this->assertDatabaseHas('advertising_campaigns', [
            'owner_id' => $superAdmin->id,
            'kos_id' => null,
            'advertiser_name' => 'Demo Moderator Ads',
            'headline' => 'Iklan Demo dari Moderator',
            'destination_url' => 'https://example.com/moderator',
            'placement' => 'marketplace',
            'status' => AdvertisingCampaign::STATUS_ACTIVE,
            'approved_by' => $superAdmin->id,
        ]);
    }

    public function test_admin_can_create_third_party_campaign(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.advertising.store'), $this->thirdPartyPayload())
            ->assertRedirect();

        $this->assertDatabaseHas('advertising_campaigns', [
            'owner_id' => $admin->id,
            'kos_id' => null,
            'status' => AdvertisingCampaign::STATUS_ACTIVE,
            'approved_by' => $admin->id,
        ]);
    }

    public function test_admin_cannot_promote_a_specific_kos_via_moderator_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);

        $this->actingAs($admin)
            ->post(route('admin.advertising.store'), $this->thirdPartyPayload(['kos_id' => $kos->id]))
            ->assertRedirect();

        $this->assertDatabaseMissing('advertising_campaigns', ['kos_id' => $kos->id]);
        $this->assertDatabaseHas('advertising_campaigns', ['kos_id' => null]);
    }

    public function test_owner_and_tenant_cannot_access_moderator_creation_routes(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($owner)->get(route('super-admin.advertising.campaigns.create'))->assertForbidden();
        $this->actingAs($owner)->get(route('admin.advertising.create'))->assertForbidden();
        $this->actingAs($tenant)->get(route('admin.advertising.create'))->assertForbidden();
        $this->actingAs($tenant)->get(route('super-admin.advertising.campaigns.create'))->assertForbidden();
    }

    public function test_admin_can_edit_third_party_campaign(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $campaign = AdvertisingCampaign::factory()->active()->thirdParty()->create([
            'owner_id' => $admin->id,
            'package_id' => $this->marketplacePackage()->id,
            'advertiser_name' => 'Nama Lama',
            'headline' => 'Headline Lama',
            'placement' => 'marketplace',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.advertising.update', $campaign), $this->thirdPartyPayload([
                'advertiser_name' => 'Nama Baru',
                'headline' => 'Headline Baru',
                'placement' => 'homepage',
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('advertising_campaigns', [
            'id' => $campaign->id,
            'advertiser_name' => 'Nama Baru',
            'headline' => 'Headline Baru',
            'placement' => 'homepage',
        ]);
        $this->assertEquals(AdvertisingCampaign::STATUS_ACTIVE, $campaign->fresh()->status);
    }

    public function test_moderator_cannot_edit_completed_third_party_campaign(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $campaign = AdvertisingCampaign::factory()->completed()->thirdParty()->create([
            'owner_id' => $superAdmin->id,
            'package_id' => $this->marketplacePackage()->id,
        ]);

        $this->actingAs($superAdmin)
            ->put(route('super-admin.advertising.campaigns.update', $campaign), $this->thirdPartyPayload())
            ->assertForbidden();
    }
}
