<?php

namespace Tests\Unit;

use App\Services\PlatformFeeService;
use Tests\TestCase;

/**
 * Test PlatformFeeService calculation logic
 */
class PlatformFeeServiceTest extends TestCase
{
    private PlatformFeeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PlatformFeeService;
    }

    // ── Fixed Amount Mode ─────────────────────────────────

    public function test_calculate_with_fixed_amount(): void
    {
        $this->mockConfig('fixed', 50000, 0);
        $fee = $this->service->calculate(1000000);
        $this->assertSame(50000.0, $fee);
    }

    public function test_calculate_fixed_zero(): void
    {
        $this->mockConfig('fixed', 0, 0);
        $fee = $this->service->calculate(1000000);
        $this->assertSame(0.0, $fee);
    }

    public function test_get_net_with_fixed_amount(): void
    {
        $this->mockConfig('fixed', 50000, 0);
        $net = $this->service->getNet(1000000);
        $this->assertSame(950000.0, $net);
    }

    public function test_calculate_for_net_with_fixed_amount(): void
    {
        $this->mockConfig('fixed', 50000, 0);
        $fee = $this->service->calculateForNet(950000);
        $this->assertSame(50000.0, $fee);
    }

    // ── Percent Mode ──────────────────────────────────────

    public function test_calculate_with_percent_rate(): void
    {
        $this->mockConfig('percent', 0, 10);
        $fee = $this->service->calculate(1000000);
        $this->assertSame(100000.0, $fee); // 10% of 1.000.000
    }

    public function test_calculate_percent_zero(): void
    {
        $this->mockConfig('percent', 0, 0);
        $fee = $this->service->calculate(1000000);
        $this->assertSame(0.0, $fee);
    }

    public function test_calculate_percent_5_percent(): void
    {
        $this->mockConfig('percent', 0, 5);
        $fee = $this->service->calculate(2000000);
        $this->assertSame(100000.0, $fee); // 5% of 2.000.000
    }

    public function test_get_net_with_percent_rate(): void
    {
        $this->mockConfig('percent', 0, 10);
        $net = $this->service->getNet(1000000);
        $this->assertSame(900000.0, $net); // 1.000.000 - 100.000
    }

    public function test_calculate_for_net_with_percent_rate(): void
    {
        $this->mockConfig('percent', 0, 10);
        // net = 900.000, gross = net / (1 - 10/100) = 900.000 / 0.9 = 1.000.000
        // fee = 1.000.000 - 900.000 = 100.000
        $fee = $this->service->calculateForNet(900000);
        $this->assertSame(100000.0, $fee);
    }

    public function test_calculate_for_net_with_percent_large_amount(): void
    {
        $this->mockConfig('percent', 0, 15);
        // Reverse calculation: net = 8.500.000
        // gross = net / (1 - 0.15) = 8.500.000 / 0.85 = 10.000.000
        // fee = 10.000.000 - 8.500.000 = 1.500.000
        $fee = $this->service->calculateForNet(8500000);
        $this->assertSame(1500000.0, $fee);
    }

    // ── Edge Cases ────────────────────────────────────────

    public function test_calculate_with_zero_amount(): void
    {
        $this->mockConfig('fixed', 50000, 0);
        $fee = $this->service->calculate(0);
        $this->assertSame(50000.0, $fee);
    }

    public function test_calculate_percent_with_zero_amount(): void
    {
        $this->mockConfig('percent', 0, 10);
        $fee = $this->service->calculate(0);
        $this->assertSame(0.0, $fee);
    }

    public function test_rounding_fixed_amount(): void
    {
        // Test that rounding works correctly
        $this->mockConfig('fixed', 33333.33, 0);
        $fee = $this->service->calculate(1000000);
        $this->assertSame(33333.33, $fee);
    }

    public function test_rounding_percent_calculation(): void
    {
        $this->mockConfig('percent', 0, 7);
        $fee = $this->service->calculate(1000000);
        $this->assertSame(70000.0, $fee);
    }

    public function test_percent_with_fractional_result(): void
    {
        $this->mockConfig('percent', 0, 15);
        // 15% of 1.000.001 = 150.000,15 → rounded to 150000.15
        $fee = $this->service->calculate(1000001);
        $this->assertSame(150000.15, $fee);
    }

    public function test_get_config_returns_default_when_not_set(): void
    {
        // When config is not mocked, should return default
        $config = $this->service->getConfig();
        $this->assertIsArray($config);
        $this->assertArrayHasKey('type', $config);
        $this->assertArrayHasKey('fixed_amount', $config);
        $this->assertArrayHasKey('percent_rate', $config);
    }

    // ── Helpers ───────────────────────────────────────────

    private function mockConfig(string $type, float $fixed, float $percent): void
    {
        $config = [
            'kosmanager' => [
                'platform_fee' => [
                    'type' => $type,
                    'fixed_amount' => $fixed,
                    'percent_rate' => $percent,
                ],
            ],
        ];

        config($config);
    }
}
