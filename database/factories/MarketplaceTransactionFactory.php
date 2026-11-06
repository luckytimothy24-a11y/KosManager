<?php

namespace Database\Factories;

use App\Models\MarketplaceTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketplaceTransaction>
 */
class MarketplaceTransactionFactory extends Factory
{
    protected $faker;

    public function definition(): array
    {
        return [
            'item_id' => null,
            'buyer_id' => null,
            'seller_id' => null,
            'price' => fake()->randomFloat(2, 100, 500000),
            'status' => 'pending',
            'notes' => null,
        ];
    }
}
