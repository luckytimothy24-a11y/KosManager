<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_code' => 'BK'.fake()->numerify('######'),
            'user_id' => User::factory(),
            'kos_id' => Kos::factory(),
            'kamar_id' => Kamar::factory(),
            'booking_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'start_date' => fake()->dateTimeBetween('now', '+1 month'),
            'end_date' => fake()->dateTimeBetween('+1 month', '+3 months'),
            'rental_type' => fake()->randomElement(['daily', 'monthly']),
            'price' => fake()->numberBetween(500000, 5000000),
            'status' => 'approved',
        ];
    }
}
