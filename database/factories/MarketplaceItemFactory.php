<?php

namespace Database\Factories;

use App\Models\MarketplaceItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Faker\Generator;

/**
 * @extends Factory<MarketplaceItem>
 */
class MarketplaceItemFactory extends Factory
{
    /**
     * Specify the Faker generator instance for this factory (used by fake() inside definition).
     */
    protected $faker;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->word(),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 100, 10000),
            'condition' => 'bekas',
            'category' => 'elektronik',
            'status' => 'available',
            'image_path' => null,
        ];
    }
}
