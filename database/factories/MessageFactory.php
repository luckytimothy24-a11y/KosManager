<?php

namespace Database\Factories;

use App\Models\Kos;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'kos_id' => Kos::factory(),
            'sender_id' => User::factory(),
            'receiver_id' => User::factory(),
            'category' => $this->faker->randomElement(array_keys(Message::CATEGORIES)),
            'message' => $this->faker->sentence(),
            'read_at' => null,
        ];
    }
}
