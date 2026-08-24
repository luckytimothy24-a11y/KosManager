<?php

namespace Database\Factories;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Penghuni>
 */
class PenghuniFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'kos_id' => Kos::factory(),
            'kamar_id' => Kamar::factory(),
            'identity_number' => fake()->numerify('################'),
            'phone' => fake()->phoneNumber(),
            'status' => 'active',
        ];
    }
}
