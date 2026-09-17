<?php

namespace Database\Factories;

use App\Models\Kos;
use App\Models\KosPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KosPhoto>
 */
class KosPhotoFactory extends Factory
{
    protected $model = KosPhoto::class;

    public function definition(): array
    {
        return [
            'kos_id' => Kos::factory(),
            'path' => 'kos/'.fake()->uuid().'.jpg',
            'position' => 0,
            'is_cover' => false,
        ];
    }

    public function cover(): static
    {
        return $this->state(fn (array $attrs) => ['is_cover' => true]);
    }
}
