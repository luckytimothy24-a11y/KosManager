<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Kos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_ok_when_database_is_available(): void
    {
        $response = $this->getJson('/health');

        $response->assertOk()
            ->assertJson([
                'status' => 'ok',
                'checks' => ['database' => 'connected'],
            ]);
    }

    public function test_up_endpoint_returns_ok_when_database_is_available(): void
    {
        $this->getJson('/up')->assertOk()->assertJson(['status' => 'ok']);
    }

    public function test_health_endpoint_is_public_and_json_only(): void
    {
        $response = $this->get('/health');

        $response->assertOk();
        $this->assertStringStartsWith('application/json', $response->headers->get('Content-Type'));
    }

    public function test_health_endpoint_returns_503_without_leaking_details_when_database_is_down(): void
    {
        config(['database.default' => 'broken_sqlite']);
        config(['database.connections.broken_sqlite' => [
            'driver' => 'sqlite',
            'database' => base_path('nonexistent-dir/db.sqlite'),
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]]);
        DB::purge('broken_sqlite');

        try {
            $response = $this->getJson('/health');

            $response->assertStatus(503)
                ->assertJson([
                    'status' => 'error',
                    'checks' => ['database' => 'unavailable'],
                ]);
            $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
            $this->assertStringNotContainsString('nonexistent-dir', $response->getContent());
            $this->assertStringNotContainsString(config('app.key'), $response->getContent());
        } finally {
            config(['database.default' => 'sqlite']);
            config(['database.connections.broken_sqlite' => null]);
            DB::purge('broken_sqlite');
        }
    }

    public function test_landing_page_searching_filter_is_preserved(): void
    {
        $kos = Kos::factory()->create(['name' => 'Kos Merdeka', 'status' => 'active']);
        Kamar::factory()->create(['kos_id' => $kos->id, 'status' => 'available', 'monthly_price' => 750000]);

        $response = $this->get('/?q=Merdeka');

        $response->assertOk();
        $response->assertSee('Kos Merdeka');
    }

    public function test_landing_page_omits_non_matching_kos_when_searching(): void
    {
        Kos::factory()->create(['name' => 'Kos Lain', 'status' => 'active']);

        $response = $this->get('/?q=tidak-ada');

        $response->assertOk();
        $response->assertDontSee('Kos Lain');
    }
}
