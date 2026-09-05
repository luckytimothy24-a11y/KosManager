<?php

namespace Tests\Feature;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingEvent;
use App\Models\AdvertisingOrder;
use App\Models\AdvertisingPackage;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use App\Services\AdvertisingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvertisingCampaignTest extends TestCase
{
    use RefreshDatabase;

    private function package(int $price = 50000, array $state = [])
    {
        return AdvertisingPackage::factory()->create(array_merge(['price' => $price], $state));
    }

    public function test_owner_can_create_campaign_for_own_kos(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $package = $this->package();

        $this->actingAs($owner)
            ->post(route('owner.advertising.store'), [
                'kos_id' => $kos->id,
                'package_id' => $package->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('advertising_campaigns', [
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
            'status' => AdvertisingCampaign::STATUS_PENDING_PAYMENT,
            'budget' => $package->price,
        ]);
    }

    public function test_owner_cannot_promote_another_owners_kos(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $otherKos = Kos::factory()->create(['owner_id' => User::factory()->create(['role' => 'owner'])->id, 'status' => 'active']);
        $package = $this->package();

        $this->actingAs($owner)
            ->post(route('owner.advertising.store'), [
                'kos_id' => $otherKos->id,
                'package_id' => $package->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('advertising_campaigns', 0);
    }

    public function test_budget_is_taken_from_server_not_client(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $package = $this->package(75000);

        $this->actingAs($owner)
            ->post(route('owner.advertising.store'), [
                'kos_id' => $kos->id,
                'package_id' => $package->id,
                // Klien mengirim nominal rendah/palsu — harus diabaikan.
                'budget' => 1,
            ]);

        $campaign = AdvertisingCampaign::where('kos_id', $kos->id)->first();
        $this->assertEquals(75000, (float) $campaign->budget);
        $this->assertNotEquals(1, (float) $campaign->budget);
    }

    public function test_inactive_package_cannot_be_used(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $package = AdvertisingPackage::factory()->inactive()->create();

        $this->actingAs($owner)
            ->post(route('owner.advertising.store'), [
                'kos_id' => $kos->id,
                'package_id' => $package->id,
            ])
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('advertising_campaigns', 0);
    }

    public function test_campaign_created_in_pending_payment_pay_transitions_to_pending_review(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $package = $this->package(60000);
        $campaign = AdvertisingCampaign::factory()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
            'status' => AdvertisingCampaign::STATUS_PENDING_PAYMENT,
            'budget' => $package->price,
        ]);

        $this->actingAs($owner)
            ->post(route('owner.advertising.pay', $campaign))
            ->assertRedirect();

        $this->assertDatabaseHas('advertising_campaigns', [
            'id' => $campaign->id,
            'status' => AdvertisingCampaign::STATUS_PENDING_REVIEW,
        ]);
        $this->assertDatabaseHas('advertising_orders', [
            'campaign_id' => $campaign->id,
            'status' => AdvertisingOrder::STATUS_PAID,
            'amount' => $package->price,
        ]);
        $this->assertEquals(1, AdvertisingOrder::where('campaign_id', $campaign->id)->where('status', 'paid')->count());
    }

    public function test_pay_is_idempotent_and_no_duplicate_orders(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $package = $this->package();
        $campaign = AdvertisingCampaign::factory()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
            'status' => AdvertisingCampaign::STATUS_PENDING_PAYMENT,
            'budget' => $package->price,
        ]);

        $service = app(AdvertisingService::class);

        // Pemanggilan pertama membuat order berstatus paid.
        $service->pay($campaign);
        $this->assertEquals(1, AdvertisingOrder::where('campaign_id', $campaign->id)->where('status', 'paid')->count());

        // Pemanggilan ulang (objek stale — simulasi double-submit) tidak menambah duplikat.
        $service->pay($campaign);
        $this->assertEquals(1, AdvertisingOrder::where('campaign_id', $campaign->id)->where('status', 'paid')->count());
        $this->assertDatabaseHas('advertising_campaigns', [
            'id' => $campaign->id,
            'status' => AdvertisingCampaign::STATUS_PENDING_REVIEW,
        ]);
    }

    public function test_owner_cannot_pay_campaign_in_wrong_state(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $package = $this->package();
        $campaign = AdvertisingCampaign::factory()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
            'status' => AdvertisingCampaign::STATUS_ACTIVE,
        ]);

        $this->actingAs($owner)
            ->post(route('owner.advertising.pay', $campaign))
            ->assertForbidden();

        $this->assertDatabaseMissing('advertising_orders', ['campaign_id' => $campaign->id]);
    }

    public function test_owner_cannot_modify_another_owners_campaign(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $other = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $other->id, 'status' => 'active']);
        $package = $this->package();
        $campaign = AdvertisingCampaign::factory()->create([
            'owner_id' => $other->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
            'status' => AdvertisingCampaign::STATUS_PENDING_PAYMENT,
        ]);

        $this->actingAs($owner)
            ->post(route('owner.advertising.pay', $campaign))
            ->assertForbidden();
    }

    public function test_super_admin_can_approve_campaign(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $package = $this->package();
        $campaign = AdvertisingCampaign::factory()->pendingReview()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(6),
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

    public function test_super_admin_can_reject_campaign(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $package = $this->package();
        $campaign = AdvertisingCampaign::factory()->pendingReview()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('super-admin.advertising.campaigns.reject', $campaign), ['reason' => 'mengandung info palsu'])
            ->assertRedirect();

        $this->assertDatabaseHas('advertising_campaigns', [
            'id' => $campaign->id,
            'status' => AdvertisingCampaign::STATUS_REJECTED,
            'rejection_reason' => 'mengandung info palsu',
        ]);
    }

    public function test_super_admin_can_suspend_active_campaign(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $package = $this->package();
        $campaign = AdvertisingCampaign::factory()->active()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('super-admin.advertising.campaigns.suspend', $campaign), ['reason' => 'lookaside'])
            ->assertRedirect();

        $this->assertDatabaseHas('advertising_campaigns', [
            'id' => $campaign->id,
            'status' => AdvertisingCampaign::STATUS_SUSPENDED,
            'suspension_reason' => 'lookaside',
        ]);
    }

    public function test_admin_can_moderate_only_assigned_kos(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'owner']);
        $assignedKos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $otherKos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $admin->assignedKos()->attach($assignedKos->id);
        $package = $this->package();

        $assignedCampaign = AdvertisingCampaign::factory()->pendingReview()->create([
            'owner_id' => $owner->id,
            'kos_id' => $assignedKos->id,
            'package_id' => $package->id,
        ]);
        $otherCampaign = AdvertisingCampaign::factory()->pendingReview()->create([
            'owner_id' => $owner->id,
            'kos_id' => $otherKos->id,
            'package_id' => $package->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.advertising.approve', $assignedCampaign))
            ->assertRedirect();
        $this->assertDatabaseHas('advertising_campaigns', ['id' => $assignedCampaign->id, 'status' => 'active']);

        $this->actingAs($admin)
            ->post(route('admin.advertising.approve', $otherCampaign))
            ->assertForbidden();
        $this->assertDatabaseHas('advertising_campaigns', ['id' => $otherCampaign->id, 'status' => 'pending_review']);
    }

    public function test_role_middleware_blocks_tenant_from_owner_advertising(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $this->actingAs($tenant)->get(route('owner.advertising.index'))->assertForbidden();
    }

    public function test_role_middleware_blocks_owner_from_super_admin_advertising(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner)->get(route('super-admin.advertising.packages.index'))->assertForbidden();
    }

    public function test_marketplace_promotes_only_live_active_partner_campaign(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = User::factory()->create(['role' => 'owner']);
        $package = $this->package(50000, ['placement' => 'marketplace']);

        // Iklan pihak ketiga aktif + live → tampil di marketplace.
        $live = AdvertisingCampaign::factory()->active()->thirdParty('marketplace')->create([
            'owner_id' => $owner->id,
            'package_id' => $package->id,
            'advertiser_name' => 'Demo Partner WiFi',
        ]);

        // Iklan pihak ketiga yang suspended / expired → TIDAK tampil.
        AdvertisingCampaign::factory()->suspended()->thirdParty('marketplace')->create([
            'owner_id' => $owner->id,
            'package_id' => $package->id,
            'advertiser_name' => 'Demo Suspended',
        ]);
        AdvertisingCampaign::factory()->expired()->thirdParty('marketplace')->create([
            'owner_id' => $owner->id,
            'package_id' => $package->id,
            'advertiser_name' => 'Demo Expired',
        ]);

        $this->actingAs($tenant)
            ->get(route('tenant.kos.index'))
            ->assertOk()
            ->assertSee('Demo Partner WiFi')
            ->assertDontSee('Demo Suspended')
            ->assertDontSee('Demo Expired');

        // Hanya iklan live yang tercatat impression-nya.
        $this->assertDatabaseHas('advertising_events', ['campaign_id' => $live->id, 'type' => 'impression']);
    }

    public function test_click_tracking_records_click_and_redirects(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $package = $this->package();
        $campaign = AdvertisingCampaign::factory()->active()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
        ]);

        $this->actingAs($tenant)
            ->get(route('tenant.ad.click', ['campaign' => $campaign->id, 'placement' => 'marketplace']))
            ->assertRedirect(route('tenant.kos.show', $kos));

        $this->assertDatabaseHas('advertising_events', [
            'campaign_id' => $campaign->id,
            'type' => 'click',
            'user_id' => $tenant->id,
        ]);
    }

    public function test_impressions_are_deduplicated(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $package = $this->package();
        $campaign = AdvertisingCampaign::factory()->active()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
        ]);

        $service = app(AdvertisingService::class);

        $service->trackEvent($campaign->id, 'impression', $tenant->id, 'sess-1', 'marketplace', true);
        $service->trackEvent($campaign->id, 'impression', $tenant->id, 'sess-1', 'marketplace', true);

        $this->assertEquals(1, AdvertisingEvent::where('campaign_id', $campaign->id)->where('type', 'impression')->count());
    }

    public function test_conversion_is_attributed_after_real_booking_following_ad_click(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $owner = User::factory()->create(['role' => 'owner']);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available']);
        $package = $this->package();
        $campaign = AdvertisingCampaign::factory()->active()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
        ]);

        $this->actingAs($tenant)->get(route('tenant.ad.click', ['campaign' => $campaign->id, 'placement' => 'marketplace']));

        $this->assertTrue(session()->has('ad_click'));

        $this->actingAs($tenant)
            ->post(route('tenant.booking.store'), [
                'kos_id' => $kos->id,
                'kamar_id' => $kamar->id,
                'start_date' => now()->addDays(1)->format('Y-m-d'),
                'end_date' => now()->addDays(5)->format('Y-m-d'),
                'rental_type' => 'daily',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('advertising_events', [
            'campaign_id' => $campaign->id,
            'type' => 'conversion',
            'user_id' => $tenant->id,
        ]);
    }
}
