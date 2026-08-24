<?php

namespace Database\Factories;

use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pembayaran>
 */
class PembayaranFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_number' => 'PY'.fake()->numerify('######'),
            'tagihan_id' => Tagihan::factory(),
            'penghuni_id' => Penghuni::factory(),
            'amount' => fake()->numberBetween(1000000, 5000000),
            'payment_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'payment_method' => fake()->randomElement(['transfer_bank', 'cash', 'e_wallet']),
            'verification_status' => 'pending',
        ];
    }
}
