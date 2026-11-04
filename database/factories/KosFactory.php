<?php

namespace Database\Factories;

use App\Models\Kos;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kos>
 */
class KosFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => fake()->company(),
            'address' => fake()->address(),
            'description' => fake()->paragraph(),
            'phone' => fake()->phoneNumber(),
            'status' => 'active',
            'auto_billing_enabled' => true,
            'billing_day' => 1,
            'due_days' => 5,
        ];
    }
}
