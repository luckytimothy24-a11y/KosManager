<?php

namespace Database\Factories;

use App\Models\MarketplaceReport;
use Illuminate\Database\Eloquence\Factories\Factory;

/**
 * @extends Factory<MarketplaceReport>
 */
class MarketplaceReportFactory extends Factory
{
    protected $faker;

    public function definition(): array
    {
        return [
            'item_id' => null,
            'reporter_id' => null,
            'reason' => 'lainnya',
            'description' => null,
            'status' => 'pending',
        ];
    }
}
