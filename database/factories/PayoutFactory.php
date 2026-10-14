<?php

namespace Database\Factories;

use App\Models\Kos;
use App\Models\PropertyOwner;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payout>
 */
class PayoutFactory extends Factory
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
            'payout_number' => 'PO-'.strtoupper(fake()->bothify('????????????')),
            'kos_id' => Kos::factory(),
            'owner_id' => User::factory()->create(['role' => 'owner']),
            'property_owner_id' => PropertyOwner::factory(),
            'gross_amount' => $gross,
            'platform_fee' => 0,
            'amount' => $gross,
            'period_start' => '2026-09-01',
            'period_end' => '2026-09-30',
            'status' => Payout::STATUS_PENDING,
        ];
    }

    public function transferred(): static
    {
        return $this->state(fn () => [
            'status' => Payout::STATUS_TRANSFERRED,
            'transferred_at' => now(),
            'proof_path' => 'payout-proof/test-proof.jpg',
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn () => [
            'status' => Payout::STATUS_VERIFIED,
            'transferred_at' => now()->subDay(),
            'verified_at' => now(),
            'proof_path' => 'payout-proof/test-proof.jpg',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => Payout::STATUS_REJECTED,
            'transferred_at' => now()->subDay(),
            'verified_at' => now(),
            'notes' => 'Bukti transfer tidak jelas',
            'proof_path' => 'payout-proof/test-proof.jpg',
        ]);
    }
}
