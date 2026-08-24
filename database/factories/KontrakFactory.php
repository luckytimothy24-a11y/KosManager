<?php

namespace Database\Factories;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Kos;
use App\Models\Penghuni;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kontrak>
 */
class KontrakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contract_number' => 'KT'.fake()->numerify('######'),
            'penghuni_id' => Penghuni::factory(),
            'kos_id' => Kos::factory(),
            'kamar_id' => Kamar::factory(),
            'rental_type' => 'monthly',
            'rental_price' => fake()->numberBetween(1000000, 5000000),
            'start_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'end_date' => fake()->dateTimeBetween('+3 months', '+6 months'),
            'status' => 'active',
        ];
    }
}
