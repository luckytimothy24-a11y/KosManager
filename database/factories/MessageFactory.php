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
            // Owner percakapan mengikuti pemilik kos saat pesan dibuat.
            // Override eksplisit jika skenario test membutuhkan owner lain.
            'owner_id' => fn (array $attributes) => Kos::find($attributes['kos_id'] ?? null)?->owner_id,
            'sender_id' => User::factory(),
            'receiver_id' => User::factory(),
            'category' => $this->faker->randomElement(array_keys(Message::CATEGORIES)),
            'message' => $this->faker->sentence(),
            'read_at' => null,
        ];
    }
}
