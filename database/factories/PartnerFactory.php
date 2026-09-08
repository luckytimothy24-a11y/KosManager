<?php

namespace Database\Factories;

use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Partner>
 */
class PartnerFactory extends Factory
{
    protected $model = Partner::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => null,
            'logo' => null,
            'description' => fake()->sentence(),
            'website_url' => 'https://'.fake()->domainName(),
            'monetization_type' => 'deal',
            'contact_name' => fake()->name(),
            'contact_email' => fake()->safeEmail(),
            'status' => Partner::STATUS_ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => Partner::STATUS_INACTIVE]);
    }
}
