<?php

namespace Database\Factories;

use App\Models\Fasilitas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fasilitas>
 */
class FasilitasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement([
            'WiFi', 'AC', 'Kamar Mandi Dalam', 'Lemari', 'Kasur',
            'Meja', 'Kursi', 'Parkir', 'CCTV', 'Water Heater',
        ]);

        return [
            'name' => $name,
            'type' => 'kamar',
            'category' => null,
            'is_active' => true,
        ];
    }
}
