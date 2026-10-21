<?php

namespace Database\Factories;

use App\Models\Topik;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Topik>
 */
class TopikFactory extends Factory
{
    protected $model = Topik::class;

    public function definition(): array
    {
        $category = fake()->randomElement(Topik::CATEGORIES);

        return [
            'name' => fake()->randomElement([
                'Kamar Standar', 'Kamar Deluxe', 'Kamar VIP',
                'Fasilitas Umum', 'Fasilitas Kamar',
                'Tagihan Sewa', 'Tagihan Listrik', 'Tagihan Air',
                'Pembayaran Online', 'Pembayaran Tunai',
                'Booking Confirm', 'Booking Pending',
                'Kontrak Tahunan', 'Kontrak Bulanan',
                'Lainnya',
            ]),
            'slug' => fake()->slug(),
            'description' => fake()->sentence(10),
            'category' => $category,
            'is_active' => true,
        ];
    }
}
