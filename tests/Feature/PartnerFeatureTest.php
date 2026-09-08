<?php

namespace Tests\Feature;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingPackage;
use App\Models\Kos;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_partner(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)
            ->post(route('super-admin.partners.store'), [
                'name' => 'DANA',
                'website_url' => 'https://www.dana.id',
                'monetization_type' => 'cpc',
                'contact_name' => 'PIC DANA',
                'contact_email' => 'partnership@dana.example',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('partners', [
            'name' => 'DANA',
            'slug' => 'dana',
            'monetization_type' => 'cpc',
            'status' => Partner::STATUS_ACTIVE,
        ]);
    }

    public function test_partner_requires_unique_slug(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        Partner::factory()->create(['name' => 'DANA', 'slug' => 'dana']);

        $this->actingAs($superAdmin)
            ->from(route('super-admin.partners.create'))
            ->post(route('super-admin.partners.store'), [
                'name' => 'DANA Lagi',
                'slug' => 'dana',
            ])
            ->assertSessionHasErrors('slug');

        $this->assertDatabaseCount('partners', 1);
    }

    public function test_super_admin_can_update_partner(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $partner = Partner::factory()->create(['name' => 'DANA', 'status' => Partner::STATUS_ACTIVE]);

        $this->actingAs($superAdmin)
            ->put(route('super-admin.partners.update', $partner), [
                'name' => 'DANA Pay',
                'monetization_type' => 'cpm',
                'status' => Partner::STATUS_INACTIVE,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'name' => 'DANA Pay',
            'slug' => 'dana-pay',
            'monetization_type' => 'cpm',
            'status' => Partner::STATUS_INACTIVE,
        ]);
    }

    public function test_owner_cannot_access_super_admin_partner_routes(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $partner = Partner::factory()->create();

        $this->actingAs($owner)
            ->get(route('super-admin.partners.index'))
            ->assertForbidden();

        $this->actingAs($owner)
            ->post(route('super-admin.partners.store'), ['name' => 'X'])
            ->assertForbidden();

        $this->actingAs($owner)
            ->delete(route('super-admin.partners.destroy', $partner))
            ->assertForbidden();
    }

    public function test_unused_partner_is_deleted(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $partner = Partner::factory()->create();

        $this->actingAs($superAdmin)
            ->delete(route('super-admin.partners.destroy', $partner))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('partners', ['id' => $partner->id]);
    }

    public function test_partner_with_campaigns_is_deactivated_not_deleted(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $owner = User::factory()->create(['role' => 'owner']);
        $partner = Partner::factory()->create(['status' => Partner::STATUS_ACTIVE]);
        $package = AdvertisingPackage::factory()->create();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);

        AdvertisingCampaign::factory()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
            'partner_id' => $partner->id,
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('super-admin.partners.destroy', $partner))
            ->assertRedirect();

        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'status' => Partner::STATUS_INACTIVE,
        ]);
    }

    public function test_campaign_can_be_linked_to_partner(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $partner = Partner::factory()->create(['name' => 'GoPay', 'monetization_type' => 'cpm']);
        $package = AdvertisingPackage::factory()->create();

        $campaign = AdvertisingCampaign::factory()->thirdParty()->create([
            'owner_id' => $owner->id,
            'package_id' => $package->id,
            'partner_id' => $partner->id,
            'advertiser_name' => 'GoPay',
            'placement' => AdvertisingCampaign::PLACEMENT_TENANT_DASHBOARD,
        ]);

        $this->assertTrue($campaign->isPartnerLinked());
        $this->assertSame('GoPay', $campaign->partnerLabel());
        $this->assertSame($partner->id, $campaign->partner->id);
        $this->assertSame(1, $partner->campaigns()->count());
        $this->assertDatabaseHas('advertising_campaigns', [
            'id' => $campaign->id,
            'partner_id' => $partner->id,
            'placement' => 'tenant_dashboard',
        ]);
    }

    public function test_super_admin_can_render_partner_pages(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $partner = Partner::factory()->create();

        $this->actingAs($superAdmin)
            ->get(route('super-admin.partners.index'))
            ->assertOk()
            ->assertSee($partner->name);

        $this->actingAs($superAdmin)
            ->get(route('super-admin.partners.create'))
            ->assertOk();

        $this->actingAs($superAdmin)
            ->get(route('super-admin.partners.edit', $partner))
            ->assertOk()
            ->assertSee($partner->name);
    }

    public function test_partner_active_scope_and_is_active_helper(): void
    {
        Partner::factory()->count(3)->create();
        Partner::factory()->inactive()->create();

        $this->assertSame(3, Partner::active()->count());
        $this->assertTrue(Partner::first()->isActive());
    }
}
