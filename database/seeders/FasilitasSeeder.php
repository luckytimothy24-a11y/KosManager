<?php

namespace Database\Seeders;

use App\Models\Fasilitas;
use Illuminate\Database\Seeder;

class FasilitasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Setiap fasilitas diberi scope (kos/kamar), icon, description, dan is_active.
     * Menggunakan updateOrCreate agar idempotent terhadap data lama.
     */
    public function run(): void
    {
        $fasilitas = [
            // Fasilitas umum Kos (scope = kos)
            ['name' => 'WiFi', 'scope' => 'kos', 'icon' => 'ri-wifi-line', 'desc' => 'WiFi tersedia untuk seluruh penghuni'],
            ['name' => 'CCTV', 'scope' => 'kos', 'icon' => 'ri-cctv-line', 'desc' => 'Area kos dipantau CCTV'],
            ['name' => 'Parkir Motor', 'scope' => 'kos', 'icon' => 'ri-motorbike-line', 'desc' => 'Area parkir khusus motor'],
            ['name' => 'Parkir Mobil', 'scope' => 'kos', 'icon' => 'ri-car-line', 'desc' => 'Area parkir khusus mobil'],
            ['name' => 'Dapur Bersama', 'scope' => 'kos', 'icon' => 'ri-restaurant-2-line', 'desc' => 'Dapur bersama untuk penghuni'],
            ['name' => 'Ruang Tamu', 'scope' => 'kos', 'icon' => 'ri-sofa-line', 'desc' => 'Ruang tamu bersama'],
            ['name' => 'Laundry', 'scope' => 'kos', 'icon' => 'ri-wallet-3-line', 'desc' => 'Layanan laundry tersedia'],
            ['name' => 'Area Jemur', 'scope' => 'kos', 'icon' => 'ri-shirt-line', 'desc' => 'Area khusus menjemur pakaian'],
            ['name' => 'Keamanan 24 Jam', 'scope' => 'kos', 'icon' => 'ri-shield-user-line', 'desc' => 'Pos keamanan tersedia 24 jam'],

            // Fasilitas Kamar (scope = kamar)
            ['name' => 'AC', 'scope' => 'kamar', 'icon' => 'ri-snowy-line', 'desc' => 'Air conditioner di dalam kamar'],
            ['name' => 'Kipas', 'scope' => 'kamar', 'icon' => 'ri-windy-line', 'desc' => 'Kipas angin di dalam kamar'],
            ['name' => 'Kasur', 'scope' => 'kamar', 'icon' => 'ri-bed-line', 'desc' => 'Kasur di dalam kamar'],
            ['name' => 'Lemari', 'scope' => 'kamar', 'icon' => 'ri-vip-diamond-line', 'desc' => 'Lemari pakaian'],
            ['name' => 'Meja Belajar', 'scope' => 'kamar', 'icon' => 'ri-bookshelf-line', 'desc' => 'Meja belajar'],
            ['name' => 'Kursi', 'scope' => 'kamar', 'icon' => 'ri-seat-line', 'desc' => 'Kursi di dalam kamar'],
            ['name' => 'Kamar Mandi Dalam', 'scope' => 'kamar', 'icon' => 'ri-drop-line', 'desc' => 'Kamar mandi pribadi di dalam kamar'],
            ['name' => 'Water Heater', 'scope' => 'kamar', 'icon' => 'ri-temperature-line', 'desc' => 'Pemanas air untuk shower'],
            ['name' => 'TV', 'scope' => 'kamar', 'icon' => 'ri-tv-2-line', 'desc' => 'Televisi di dalam kamar'],
        ];

        foreach ($fasilitas as $item) {
            Fasilitas::updateOrCreate(
                ['name' => $item['name']],
                [
                    'type' => $item['scope'],
                    'icon' => $item['icon'],
                    'description' => $item['desc'],
                    'is_active' => true,
                ]
            );
        }
    }
}
