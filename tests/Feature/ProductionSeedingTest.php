<?php

namespace Tests\Feature;

use App\Models\Kos;
use App\Models\User;
use Database\Seeders\AdvertisingSeeder;
use Database\Seeders\BookingSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionSeedingTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_database_seeder_skips_advertising_seeder(): void
    {
        $this->app->instance('env', 'production');

        $seeders = (new DatabaseSeeder)->seeders();

        $this->assertNotContains(AdvertisingSeeder::class, $seeders);
        $this->assertContains(BookingSeeder::class, $seeders);
        $this->assertSame(UserSeeder::class, $seeders[0]);
    }

    public function test_database_seeder_keeps_advertising_seeder_outside_production(): void
    {
        $this->assertNotEquals('production', app()->environment());

        $seeders = (new DatabaseSeeder)->seeders();

        $this->assertContains(AdvertisingSeeder::class, $seeders);
    }

    public function test_production_seeding_never_creates_paid_advertising_demo_data(): void
    {
        $this->app->instance('env', 'production');

        $this->artisan('db:seed', ['--force' => true]);

        $this->assertDatabaseCount('advertising_campaigns', 0);
        $this->assertDatabaseCount('advertising_orders', 0);
    }

    public function test_advertising_seeder_fails_closed_in_production(): void
    {
        $this->app->instance('env', 'production');

        try {
            $this->artisan('db:seed', ['--class' => AdvertisingSeeder::class, '--force' => true]);
            $this->fail('AdvertisingSeeder harus gagal di environment production.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsStringIgnoringCase('production', $e->getMessage());
        }

        $this->assertDatabaseCount('advertising_campaigns', 0);
        $this->assertDatabaseCount('advertising_orders', 0);
    }

    public function test_local_seeding_still_creates_paid_demo_orders(): void
    {
        $owner = User::factory()->create(['email' => 'owner@example.com', 'role' => 'owner']);
        $secondOwner = User::factory()->create(['email' => 'gnamaga@example.com', 'role' => 'owner']);
        User::factory()->create(['role' => 'super_admin']);

        Kos::factory()->create(['name' => 'Kos Melati', 'owner_id' => $owner->id, 'status' => 'active']);

        $this->seed(AdvertisingSeeder::class);

        $this->assertDatabaseHas('advertising_campaigns', ['campaign_number' => 'AD-DEMO-1']);
        $this->assertDatabaseHas('advertising_orders', ['status' => 'paid']);
    }
}
