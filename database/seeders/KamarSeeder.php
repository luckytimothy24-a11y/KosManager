<?php

namespace Database\Seeders;

use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Kos;
use Illuminate\Database\Seeder;

class KamarSeeder extends Seeder
{
    public function run(): void
    {
        $allFacilities = Fasilitas::all();

        $roomPhotos = [
            'Kost Nawasena Seturan' => [
                'A-01' => 'kamar/kamar-single.jpg',
                'A-02' => 'kamar/kamar-single.jpg',
                'A-03' => 'kamar/kamar-standard-1.jpg',
                'B-01' => 'kamar/kamar-deluxe-1.jpg',
                'B-02' => 'kamar/kamar-standard-2.jpg',
                'B-03' => 'kamar/kamar-deluxe-2.jpg',
            ],
            'Kos Arunika Babarsari' => [
                '101' => 'kamar/kamar-standard-1.jpg',
                '102' => 'kamar/kamar-standard-2.jpg',
                '103' => 'kamar/kamar-single.jpg',
                '201' => 'kamar/kamar-deluxe-1.jpg',
                '202' => 'kamar/kamar-standard-2.jpg',
                '203' => 'kamar/kamar-vip-1.jpg',
                '204' => 'kamar/kamar-deluxe-2.jpg',
            ],
            'Kos Maleo Residence' => [
                'M-01' => 'kamar/kamar-standard-1.jpg',
                'M-02' => 'kamar/kamar-standard-2.jpg',
                'M-03' => 'kamar/kamar-vip-2.jpg',
                'M-04' => 'kamar/kamar-single.jpg',
                'M-05' => 'kamar/kamar-vip-1.jpg',
                'M-06' => 'kamar/kamar-standard-1.jpg',
            ],
            'Kost Taman Gejayan' => [
                'TG-01' => 'kamar/kamar-standard-1.jpg',
                'TG-02' => 'kamar/kamar-standard-2.jpg',
                'TG-03' => 'kamar/kamar-single.jpg',
                'TG-04' => 'kamar/kamar-deluxe-1.jpg',
                'TG-05' => 'kamar/kamar-vip-2.jpg',
                'TG-06' => 'kamar/kamar-standard-2.jpg',
            ],
            'Rumah Kos Srikandi' => [
                'SR-01' => 'kamar/kamar-single.jpg',
                'SR-02' => 'kamar/kamar-single.jpg',
                'SR-03' => 'kamar/kamar-standard-1.jpg',
                'SR-04' => 'kamar/kamar-standard-2.jpg',
                'SR-05' => 'kamar/kamar-vip-1.jpg',
            ],
            'Kos Pondok Pogung' => [
                'P-01' => 'kamar/kamar-standard-1.jpg',
                'P-02' => 'kamar/kamar-standard-2.jpg',
                'P-03' => 'kamar/kamar-single.jpg',
                'P-04' => 'kamar/kamar-deluxe-1.jpg',
                'P-05' => 'kamar/kamar-standard-2.jpg',
                'P-06' => 'kamar/kamar-vip-2.jpg',
                'P-07' => 'kamar/kamar-single.jpg',
                'P-08' => 'kamar/kamar-vip-1.jpg',
            ],
            'Kost Cendana Jakal' => [
                'CJ-01' => 'kamar/kamar-standard-1.jpg',
                'CJ-02' => 'kamar/kamar-standard-2.jpg',
                'CJ-03' => 'kamar/kamar-single.jpg',
                'CJ-04' => 'kamar/kamar-deluxe-2.jpg',
                'CJ-05' => 'kamar/kamar-standard-1.jpg',
            ],
            'Kos Adikara Residence' => [
                'AD-01' => 'kamar/kamar-standard-1.jpg',
                'AD-02' => 'kamar/kamar-standard-2.jpg',
                'AD-03' => 'kamar/kamar-vip-1.jpg',
                'AD-04' => 'kamar/kamar-single.jpg',
                'AD-05' => 'kamar/kamar-vip-2.jpg',
                'AD-06' => 'kamar/kamar-deluxe-1.jpg',
                'AD-07' => 'kamar/kamar-standard-2.jpg',
                'AD-08' => 'kamar/kamar-standard-1.jpg',
            ],
        ];

        $kosRooms = [
            'Kost Nawasena Seturan' => [
                ['number' => 'A-01', 'name' => 'Standard Single', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 850000, 'weekly' => 220000, 'area' => '10m2', 'floor' => 1],
                ['number' => 'A-02', 'name' => 'Standard Single', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 850000, 'weekly' => 220000, 'area' => '10m2', 'floor' => 1],
                ['number' => 'A-03', 'name' => 'Twin Sharing', 'type' => 'Sharing', 'capacity' => 2, 'monthly' => 700000, 'weekly' => 180000, 'area' => '14m2', 'floor' => 1],
                ['number' => 'B-01', 'name' => 'Deluxe Single', 'type' => 'Deluxe', 'capacity' => 1, 'monthly' => 1250000, 'weekly' => 320000, 'area' => '16m2', 'floor' => 2],
                ['number' => 'B-02', 'name' => 'Triple Sharing', 'type' => 'Sharing', 'capacity' => 3, 'monthly' => 650000, 'weekly' => 170000, 'area' => '18m2', 'floor' => 2],
                ['number' => 'B-03', 'name' => 'Deluxe Single', 'type' => 'Deluxe', 'capacity' => 1, 'monthly' => 1250000, 'weekly' => 320000, 'area' => '16m2', 'floor' => 2],
            ],
            'Kos Arunika Babarsari' => [
                ['number' => '101', 'name' => 'Standard', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 1000000, 'weekly' => 260000, 'area' => '12m2', 'floor' => 1],
                ['number' => '102', 'name' => 'Standard', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 1000000, 'weekly' => 260000, 'area' => '12m2', 'floor' => 1],
                ['number' => '103', 'name' => 'Twin Bed', 'type' => 'Sharing', 'capacity' => 2, 'monthly' => 850000, 'weekly' => 220000, 'area' => '16m2', 'floor' => 1],
                ['number' => '201', 'name' => 'Deluxe', 'type' => 'Deluxe', 'capacity' => 1, 'monthly' => 1500000, 'weekly' => 390000, 'area' => '18m2', 'floor' => 2],
                ['number' => '202', 'name' => 'Triple Sharing', 'type' => 'Sharing', 'capacity' => 3, 'monthly' => 750000, 'weekly' => 195000, 'area' => '20m2', 'floor' => 2],
                ['number' => '203', 'name' => 'Family Room', 'type' => 'Family', 'capacity' => 4, 'monthly' => 2000000, 'weekly' => 520000, 'area' => '28m2', 'floor' => 2],
                ['number' => '204', 'name' => 'Deluxe', 'type' => 'Deluxe', 'capacity' => 1, 'monthly' => 1500000, 'weekly' => 390000, 'area' => '18m2', 'floor' => 2],
            ],
            'Kos Maleo Residence' => [
                ['number' => 'M-01', 'name' => 'Standard AC', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 1200000, 'weekly' => 310000, 'area' => '12m2', 'floor' => 1],
                ['number' => 'M-02', 'name' => 'Standard AC', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 1200000, 'weekly' => 310000, 'area' => '12m2', 'floor' => 1],
                ['number' => 'M-03', 'name' => 'Deluxe AC', 'type' => 'Deluxe', 'capacity' => 1, 'monthly' => 1750000, 'weekly' => 450000, 'area' => '16m2', 'floor' => 1],
                ['number' => 'M-04', 'name' => 'Twin Sharing', 'type' => 'Sharing', 'capacity' => 2, 'monthly' => 1000000, 'weekly' => 260000, 'area' => '18m2', 'floor' => 2],
                ['number' => 'M-05', 'name' => 'VIP Suite', 'type' => 'VIP', 'capacity' => 1, 'monthly' => 2500000, 'weekly' => 650000, 'area' => '24m2', 'floor' => 2],
                ['number' => 'M-06', 'name' => 'Triple Sharing', 'type' => 'Sharing', 'capacity' => 3, 'monthly' => 900000, 'weekly' => 235000, 'area' => '22m2', 'floor' => 2],
            ],
            'Kost Taman Gejayan' => [
                ['number' => 'TG-01', 'name' => 'Standard', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 900000, 'weekly' => 235000, 'area' => '11m2', 'floor' => 1],
                ['number' => 'TG-02', 'name' => 'Standard', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 900000, 'weekly' => 235000, 'area' => '11m2', 'floor' => 1],
                ['number' => 'TG-03', 'name' => 'Twin Sharing', 'type' => 'Sharing', 'capacity' => 2, 'monthly' => 750000, 'weekly' => 195000, 'area' => '15m2', 'floor' => 1],
                ['number' => 'TG-04', 'name' => 'Deluxe', 'type' => 'Deluxe', 'capacity' => 1, 'monthly' => 1400000, 'weekly' => 365000, 'area' => '18m2', 'floor' => 2],
                ['number' => 'TG-05', 'name' => 'Family Room', 'type' => 'Family', 'capacity' => 4, 'monthly' => 1750000, 'weekly' => 455000, 'area' => '26m2', 'floor' => 2],
                ['number' => 'TG-06', 'name' => 'Triple Sharing', 'type' => 'Sharing', 'capacity' => 3, 'monthly' => 700000, 'weekly' => 185000, 'area' => '20m2', 'floor' => 2],
            ],
            'Rumah Kos Srikandi' => [
                ['number' => 'SR-01', 'name' => 'Kamar Depan', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 700000, 'weekly' => 180000, 'area' => '10m2', 'floor' => 1],
                ['number' => 'SR-02', 'name' => 'Kamar Belakang', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 650000, 'weekly' => 170000, 'area' => '9m2', 'floor' => 1],
                ['number' => 'SR-03', 'name' => 'Kamar Tengah', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 700000, 'weekly' => 180000, 'area' => '11m2', 'floor' => 1],
                ['number' => 'SR-04', 'name' => 'Twin Room', 'type' => 'Sharing', 'capacity' => 2, 'monthly' => 600000, 'weekly' => 155000, 'area' => '14m2', 'floor' => 2],
                ['number' => 'SR-05', 'name' => 'Family Room', 'type' => 'Family', 'capacity' => 4, 'monthly' => 1500000, 'weekly' => 390000, 'area' => '24m2', 'floor' => 2],
            ],
            'Kos Pondok Pogung' => [
                ['number' => 'P-01', 'name' => 'Standard', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 1100000, 'weekly' => 285000, 'area' => '12m2', 'floor' => 1],
                ['number' => 'P-02', 'name' => 'Standard', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 1100000, 'weekly' => 285000, 'area' => '12m2', 'floor' => 1],
                ['number' => 'P-03', 'name' => 'Twin Bed', 'type' => 'Sharing', 'capacity' => 2, 'monthly' => 900000, 'weekly' => 235000, 'area' => '16m2', 'floor' => 1],
                ['number' => 'P-04', 'name' => 'Deluxe', 'type' => 'Deluxe', 'capacity' => 1, 'monthly' => 1600000, 'weekly' => 415000, 'area' => '20m2', 'floor' => 2],
                ['number' => 'P-05', 'name' => 'Triple Sharing', 'type' => 'Sharing', 'capacity' => 3, 'monthly' => 800000, 'weekly' => 210000, 'area' => '20m2', 'floor' => 2],
                ['number' => 'P-06', 'name' => 'Family Room', 'type' => 'Family', 'capacity' => 4, 'monthly' => 2200000, 'weekly' => 570000, 'area' => '30m2', 'floor' => 2],
                ['number' => 'P-07', 'name' => 'Standard', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 1100000, 'weekly' => 285000, 'area' => '12m2', 'floor' => 1],
                ['number' => 'P-08', 'name' => 'VIP Suite', 'type' => 'VIP', 'capacity' => 2, 'monthly' => 2500000, 'weekly' => 650000, 'area' => '24m2', 'floor' => 2],
            ],
            'Kost Cendana Jakal' => [
                ['number' => 'CJ-01', 'name' => 'Standard', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 800000, 'weekly' => 210000, 'area' => '10m2', 'floor' => 1],
                ['number' => 'CJ-02', 'name' => 'Standard', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 800000, 'weekly' => 210000, 'area' => '10m2', 'floor' => 1],
                ['number' => 'CJ-03', 'name' => 'Twin Sharing', 'type' => 'Sharing', 'capacity' => 2, 'monthly' => 700000, 'weekly' => 180000, 'area' => '14m2', 'floor' => 1],
                ['number' => 'CJ-04', 'name' => 'Deluxe', 'type' => 'Deluxe', 'capacity' => 1, 'monthly' => 1300000, 'weekly' => 340000, 'area' => '16m2', 'floor' => 2],
                ['number' => 'CJ-05', 'name' => 'Triple Sharing', 'type' => 'Sharing', 'capacity' => 3, 'monthly' => 650000, 'weekly' => 170000, 'area' => '18m2', 'floor' => 2],
            ],
            'Kos Adikara Residence' => [
                ['number' => 'AD-01', 'name' => 'Standard AC', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 1500000, 'weekly' => 390000, 'area' => '14m2', 'floor' => 1],
                ['number' => 'AD-02', 'name' => 'Standard AC', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 1500000, 'weekly' => 390000, 'area' => '14m2', 'floor' => 1],
                ['number' => 'AD-03', 'name' => 'Deluxe AC', 'type' => 'Deluxe', 'capacity' => 1, 'monthly' => 2000000, 'weekly' => 520000, 'area' => '18m2', 'floor' => 1],
                ['number' => 'AD-04', 'name' => 'Twin Sharing', 'type' => 'Sharing', 'capacity' => 2, 'monthly' => 1250000, 'weekly' => 325000, 'area' => '18m2', 'floor' => 2],
                ['number' => 'AD-05', 'name' => 'VIP Suite', 'type' => 'VIP', 'capacity' => 1, 'monthly' => 2500000, 'weekly' => 650000, 'area' => '24m2', 'floor' => 2],
                ['number' => 'AD-06', 'name' => 'Family Room', 'type' => 'Family', 'capacity' => 4, 'monthly' => 3000000, 'weekly' => 780000, 'area' => '32m2', 'floor' => 2],
                ['number' => 'AD-07', 'name' => 'Triple Sharing', 'type' => 'Sharing', 'capacity' => 3, 'monthly' => 1100000, 'weekly' => 285000, 'area' => '22m2', 'floor' => 2],
                ['number' => 'AD-08', 'name' => 'Standard AC', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 1500000, 'weekly' => 390000, 'area' => '14m2', 'floor' => 3],
            ],
        ];

        $roomPhotos = [
            'Kost Nawasena Seturan' => [
                'A-01' => 'kamar/kamar-single.jpg',
                'A-02' => 'kamar/kamar-single.jpg',
                'A-03' => 'kamar/kamar-standard-1.jpg',
                'B-01' => 'kamar/kamar-deluxe-1.jpg',
                'B-02' => 'kamar/kamar-standard-2.jpg',
                'B-03' => 'kamar/kamar-deluxe-2.jpg',
            ],
            'Kos Arunika Babarsari' => [
                '101' => 'kamar/kamar-standard-1.jpg',
                '102' => 'kamar/kamar-standard-2.jpg',
                '103' => 'kamar/kamar-single.jpg',
                '201' => 'kamar/kamar-deluxe-1.jpg',
                '202' => 'kamar/kamar-standard-2.jpg',
                '203' => 'kamar/kamar-vip-1.jpg',
                '204' => 'kamar/kamar-deluxe-2.jpg',
            ],
            'Kos Maleo Residence' => [
                'M-01' => 'kamar/kamar-standard-1.jpg',
                'M-02' => 'kamar/kamar-standard-2.jpg',
                'M-03' => 'kamar/kamar-vip-2.jpg',
                'M-04' => 'kamar/kamar-single.jpg',
                'M-05' => 'kamar/kamar-vip-1.jpg',
                'M-06' => 'kamar/kamar-standard-1.jpg',
            ],
            'Kost Taman Gejayan' => [
                'TG-01' => 'kamar/kamar-standard-1.jpg',
                'TG-02' => 'kamar/kamar-standard-2.jpg',
                'TG-03' => 'kamar/kamar-single.jpg',
                'TG-04' => 'kamar/kamar-deluxe-1.jpg',
                'TG-05' => 'kamar/kamar-vip-2.jpg',
                'TG-06' => 'kamar/kamar-standard-2.jpg',
            ],
            'Rumah Kos Srikandi' => [
                'SR-01' => 'kamar/kamar-single.jpg',
                'SR-02' => 'kamar/kamar-single.jpg',
                'SR-03' => 'kamar/kamar-standard-1.jpg',
                'SR-04' => 'kamar/kamar-standard-2.jpg',
                'SR-05' => 'kamar/kamar-vip-1.jpg',
            ],
            'Kos Pondok Pogung' => [
                'P-01' => 'kamar/kamar-standard-1.jpg',
                'P-02' => 'kamar/kamar-standard-2.jpg',
                'P-03' => 'kamar/kamar-single.jpg',
                'P-04' => 'kamar/kamar-deluxe-1.jpg',
                'P-05' => 'kamar/kamar-standard-2.jpg',
                'P-06' => 'kamar/kamar-vip-2.jpg',
                'P-07' => 'kamar/kamar-single.jpg',
                'P-08' => 'kamar/kamar-vip-1.jpg',
            ],
            'Kost Cendana Jakal' => [
                'CJ-01' => 'kamar/kamar-standard-1.jpg',
                'CJ-02' => 'kamar/kamar-standard-2.jpg',
                'CJ-03' => 'kamar/kamar-single.jpg',
                'CJ-04' => 'kamar/kamar-deluxe-2.jpg',
                'CJ-05' => 'kamar/kamar-standard-1.jpg',
            ],
            'Kos Adikara Residence' => [
                'AD-01' => 'kamar/kamar-standard-1.jpg',
                'AD-02' => 'kamar/kamar-standard-2.jpg',
                'AD-03' => 'kamar/kamar-vip-1.jpg',
                'AD-04' => 'kamar/kamar-single.jpg',
                'AD-05' => 'kamar/kamar-vip-2.jpg',
                'AD-06' => 'kamar/kamar-deluxe-1.jpg',
                'AD-07' => 'kamar/kamar-standard-2.jpg',
                'AD-08' => 'kamar/kamar-standard-1.jpg',
            ],
        ];

        foreach ($kosRooms as $kosName => $rooms) {
            $kos = Kos::where('name', $kosName)->first();
            if (! $kos) {
                continue;
            }

            foreach ($rooms as $room) {
                $isSharing = ($room['type'] === 'Sharing' || ($room['capacity'] ?? 1) > 1);
                $photoPath = $roomPhotos[$kosName][$room['number']] ?? null;
                $kamar = Kamar::create([
                    'kos_id' => $kos->id,
                    'room_number' => $room['number'],
                    'room_name' => $room['name'],
                    'room_type' => $room['type'],
                    'capacity' => $room['capacity'],
                    'floor' => $room['floor'],
                    'daily_price' => (int) ceil($room['monthly'] / 30),
                    'weekly_price' => $room['weekly'],
                    'monthly_price' => $room['monthly'],
                    'area' => $room['area'],
                    'billing_mode' => $isSharing ? 'shared' : 'personal',
                    'status' => 'available',
                    'photo' => $photoPath,
                ]);

                $facilityCount = match ($room['type']) {
                    'VIP' => min(7, $allFacilities->count()),
                    'Deluxe' => min(5, $allFacilities->count()),
                    'Family' => min(6, $allFacilities->count()),
                    'Sharing' => min(4, $allFacilities->count()),
                    default => min(3, $allFacilities->count()),
                };

                if ($facilityCount > 0 && $allFacilities->count() > 0) {
                    $kamar->fasilitas()->sync(
                        $allFacilities->random($facilityCount)->pluck('id')->toArray()
                    );
                }
            }
        }
    }
}
