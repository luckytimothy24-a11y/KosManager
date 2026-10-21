<?php

namespace Database\Factories;

use App\Models\PropertyOwner;
use App\Models\PropertyOwnerBankAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyOwnerBankAccount>
 */
class PropertyOwnerBankAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_owner_id' => PropertyOwner::factory(),
            'bank_name' => fake()->randomElement(['BCA', 'MANDIRI', 'BRI', 'BNI']),
            'account_number' => fake()->numerify('00################'),
            'account_holder_name' => fake()->name(),
        ];
    }
}
