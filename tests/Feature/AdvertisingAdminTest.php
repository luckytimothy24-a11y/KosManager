<?php

namespace Tests\Feature;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingOrder;
use App\Models\AdvertisingPackage;
use App\Models\Kos;
use App\Models\User;
use Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvertisingAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_package(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)
            ->post(route('super-admin.advertising.packages.store'), [
                'code' => 'basic',
                'name' => 'Basic Promo',
                'price' => 25000,
                'duration_days' => 7,
                'is_sponsored' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('advertising_packages', [
            'code' => 'BASIC',
            'name' => 'Basic Promo',
            'price' => 25000,
            'duration_days' => 7,
            'is_sponsored' => true,
            'is_featured' => false,
        ]);
    }

    public function test_package_price_is_never_hardcoded_in_store(): void
    {
        // Harga harus datang dari validasi + DB, bukan angka tetap di controller.
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)
            ->post(route('super-admin.advertising.packages.store'), [
                'code' => 'custom',
                'name' => 'Custom',
                'price' => 12345,
                'duration_days' => 3,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('advertising_packages', ['code' => 'CUSTOM', 'price' => 12345]);
    }

    public function test_used_package_is_deactivated_not_deleted(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $owner = User::factory()->create(['role' => 'owner']);
        $package = AdvertisingPackage::factory()->create();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        AdvertisingCampaign::factory()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('super-admin.advertising.packages.destroy', $package))
            ->assertRedirect();

        $this->assertDatabaseHas('advertising_packages', ['id' => $package->id, 'is_active' => false]);
    }

    public function test_unused_package_is_deleted(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $package = AdvertisingPackage::factory()->create();

        $this->actingAs($superAdmin)
            ->delete(route('super-admin.advertising.packages.destroy', $package))
            ->assertRedirect();

        $this->assertDatabaseMissing('advertising_packages', ['id' => $package->id]);
    }

    public function test_revenue_report_lists_paid_orders(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $owner = User::factory()->create(['role' => 'owner']);
        $package = AdvertisingPackage::factory()->create(['price' => 75000]);
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $campaign = AdvertisingCampaign::factory()->active()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
            'budget' => 75000,
        ]);
        AdvertisingOrder::factory()->create([
            'campaign_id' => $campaign->id,
            'owner_id' => $owner->id,
            'amount' => 75000,
            'status' => AdvertisingOrder::STATUS_PAID,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('super-admin.advertising.revenue'))
            ->assertOk()
            ->assertSee('Rp 75.000')
            ->assertSee($campaign->campaign_number);
    }

    public function test_owner_advertising_dashboard_renders(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $package = AdvertisingPackage::factory()->create();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        AdvertisingCampaign::factory()->active()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
        ]);

        $this->actingAs($owner)
            ->get(route('owner.advertising.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Advertising');
    }

    public function test_scheduler_activates_approved_campaigns_at_start(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $package = AdvertisingPackage::factory()->create();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);

        $approved = AdvertisingCampaign::factory()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
            'status' => AdvertisingCampaign::STATUS_APPROVED,
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addDays(3),
        ]);

        // Approved yang belum waktunya tayang tidak diaktifkan.
        $future = AdvertisingCampaign::factory()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
            'status' => AdvertisingCampaign::STATUS_APPROVED,
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(5),
        ]);

        Artisan::call('advertising:process-campaigns');

        $this->assertDatabaseHas('advertising_campaigns', ['id' => $approved->id, 'status' => 'active']);
        $this->assertDatabaseHas('advertising_campaigns', ['id' => $future->id, 'status' => 'approved']);
    }

    public function test_scheduler_completes_expired_campaigns(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $package = AdvertisingPackage::factory()->create();
        $kos = Kos::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);

        $expired = AdvertisingCampaign::factory()->expired()->create([
            'owner_id' => $owner->id,
            'kos_id' => $kos->id,
            'package_id' => $package->id,
        ]);

        Artisan::call('advertising:process-campaigns');

        $this->assertDatabaseHas('advertising_campaigns', ['id' => $expired->id, 'status' => 'completed']);
    }
}
