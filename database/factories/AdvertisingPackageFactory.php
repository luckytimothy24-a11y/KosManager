<?php

namespace Database\Factories;

use App\Models\AdvertisingPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdvertisingPackage>
 */
class AdvertisingPackageFactory extends Factory
{
    protected $model = AdvertisingPackage::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify('PKG????')),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'price' => 50000,
            'duration_days' => 7,
            'is_featured' => false,
            'is_sponsored' => true,
            'is_homepage' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => [
            'is_featured' => true,
            'is_sponsored' => true,
        ]);
    }

    public function homepage(): static
    {
        return $this->state(fn () => [
            'is_featured' => true,
            'is_sponsored' => true,
            'is_homepage' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
