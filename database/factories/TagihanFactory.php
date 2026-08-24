<?php

namespace Database\Factories;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Penghuni;
use App\Models\Tagihan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tagihan>
 */
class TagihanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bill_number' => 'TG'.fake()->numerify('######'),
            'penghuni_id' => Penghuni::factory(),
            'kontrak_id' => Kontrak::factory(),
            'kamar_id' => Kamar::factory(),
            'bill_type' => 'rent',
            'period_start' => fake()->dateTimeBetween('-1 month', 'now'),
            'period_end' => fake()->dateTimeBetween('now', '+1 month'),
            'subtotal' => fake()->numberBetween(1000000, 5000000),
            'discount' => 0,
            'penalty' => 0,
            'total' => fake()->numberBetween(1000000, 5000000),
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
            'status' => 'unpaid',
        ];
    }
}
