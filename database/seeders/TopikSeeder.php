<?php

namespace Database\Seeders;

use App\Models\Topik;
use Illuminate\Database\Seeder;

class TopikSeeder extends Seeder
{
    public function run(): void
    {
        $topik = [
            [
                'name' => 'Kamar',
                'slug' => 'kamar',
                'description' => 'Topik untuk kategori kamar',
                'category' => Topik::CATEGORY_KAMAR,
                'is_active' => true,
            ],
            [
                'name' => 'Fasilitas',
                'slug' => 'fasilitas',
                'description' => 'Topik untuk kategori fasilitas',
                'category' => Topik::CATEGORY_FASILITAS,
                'is_active' => true,
            ],
            [
                'name' => 'Tagihan',
                'slug' => 'tagihan',
                'description' => 'Topik untuk kategori tagihan',
                'category' => Topik::CATEGORY_TAGIHAN,
                'is_active' => true,
            ],
            [
                'name' => 'Pembayaran',
                'slug' => 'pembayaran',
                'description' => 'Topik untuk kategori pembayaran',
                'category' => Topik::CATEGORY_PEMBAYARAN,
                'is_active' => true,
            ],
            [
                'name' => 'Booking',
                'slug' => 'booking',
                'description' => 'Topik untuk kategori booking',
                'category' => Topik::CATEGORY_BOOKING,
                'is_active' => true,
            ],
            [
                'name' => 'Kontrak',
                'slug' => 'kontrak',
                'description' => 'Topik untuk kategori kontrak',
                'category' => Topik::CATEGORY_KONTRAK,
                'is_active' => true,
            ],
            [
                'name' => 'Lainnya',
                'slug' => 'lainnya',
                'description' => 'Topik untuk kategori lainnya',
                'category' => Topik::CATEGORY_LAINNYA,
                'is_active' => true,
            ],
        ];

        foreach ($topik as $item) {
            Topik::updateOrCreate(
                ['slug' => $item['slug']],
                $item
            );
        }
    }
}
