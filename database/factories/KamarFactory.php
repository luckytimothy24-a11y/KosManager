<?php

namespace Database\Factories;

use App\Models\Kamar;
use App\Models\Kos;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kamar>
 */
class KamarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kos_id' => Kos::factory(),
            'room_number' => fake()->numerify('###'),
            'room_name' => 'Kamar '.fake()->randomLetter(),
            'floor' => fake()->numberBetween(1, 5),
            'room_type' => fake()->randomElement(['Standard', 'Deluxe', 'VIP']),
            'daily_price' => fake()->numberBetween(100000, 500000),
            'monthly_price' => fake()->numberBetween(1000000, 5000000),
            'area' => fake()->randomElement(['12m2', '16m2', '20m2', '24m2']),
            'status' => 'available',
        ];
    }
}
