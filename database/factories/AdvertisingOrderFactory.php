<?php

namespace Database\Factories;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdvertisingOrder>
 */
class AdvertisingOrderFactory extends Factory
{
    protected $model = AdvertisingOrder::class;

    public function definition(): array
    {
        return [
            'order_number' => 'AO'.fake()->numerify('######'),
            'campaign_id' => AdvertisingCampaign::factory(),
            'owner_id' => User::factory(),
            'amount' => 50000,
            'status' => AdvertisingOrder::STATUS_PAID,
            'paid_at' => now(),
        ];
    }
}
