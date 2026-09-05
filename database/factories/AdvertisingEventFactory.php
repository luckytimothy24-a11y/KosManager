<?php

namespace Database\Factories;

use App\Models\AdvertisingCampaign;
use App\Models\AdvertisingEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdvertisingEvent>
 */
class AdvertisingEventFactory extends Factory
{
    protected $model = AdvertisingEvent::class;

    public function definition(): array
    {
        return [
            'campaign_id' => AdvertisingCampaign::factory(),
            'type' => AdvertisingEvent::TYPE_IMPRESSION,
            'user_id' => User::factory(),
            'session_key' => fake()->uuid(),
            'placement' => 'marketplace',
            'created_at' => now(),
        ];
    }

    public function impression(): static
    {
        return $this->state(fn () => ['type' => AdvertisingEvent::TYPE_IMPRESSION]);
    }

    public function click(): static
    {
        return $this->state(fn () => ['type' => AdvertisingEvent::TYPE_CLICK]);
    }

    public function conversion(): static
    {
        return $this->state(fn () => ['type' => AdvertisingEvent::TYPE_CONVERSION]);
    }
}
