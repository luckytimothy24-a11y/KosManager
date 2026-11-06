<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\PaymentDistribution;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentDistribution>
 */
class PaymentDistributionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gross = fake()->numberBetween(1000000, 5000000);

        return [
            'pembayaran_id' => Pembayaran::factory(),
            'booking_id' => Booking::factory(),
            'owner_id' => User::factory()->create(['role' => 'owner']),
            'gross_amount' => $gross,
            'platform_fee' => 0,
            'owner_amount' => $gross,
            'status' => PaymentDistribution::STATUS_PENDING,
            'processed_at' => null,
        ];
    }

    public function ready(): static
    {
        return $this->state(fn () => [
            'status' => PaymentDistribution::STATUS_READY,
        ]);
    }

    public function processed(): static
    {
        return $this->state(fn () => [
            'status' => PaymentDistribution::STATUS_PROCESSED,
            'processed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => PaymentDistribution::STATUS_FAILED,
        ]);
    }
}
