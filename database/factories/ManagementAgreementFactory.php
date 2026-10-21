<?php

namespace Database\Factories;

use App\Models\Kos;
use App\Models\ManagementAgreement;
use App\Models\PropertyOwner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ManagementAgreement>
 */
class ManagementAgreementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kos_id' => Kos::factory(),
            'property_owner_id' => PropertyOwner::factory(),
            'commission_type' => 'percent',
            'commission_value' => fake()->randomElement([5, 10, 15]),
            'effective_from' => fake()->dateTimeBetween('-2 months', 'now'),
            'effective_to' => null,
        ];
    }
}
