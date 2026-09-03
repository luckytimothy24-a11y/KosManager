<?php

namespace Database\Seeders;

use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Kos;
use Illuminate\Database\Seeder;

class KamarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            'A' => ['A01', 'A02', 'A03', 'A04', 'A05'],
            'B' => ['B01', 'B02', 'B03', 'B04', 'B05'],
            'C' => ['C01', 'C02', 'C03', 'C04', 'C05'],
        ];

        $types = ['Standard', 'Deluxe', 'VIP'];
        $areas = ['12m2', '16m2', '20m2', '24m2'];

        $kosList = Kos::all();
        $allFacilities = Fasilitas::all();
        $prefixes = ['A', 'B', 'C'];

        foreach ($kosList as $index => $kos) {
            $prefix = $prefixes[$index];

            foreach ($rooms[$prefix] as $roomNumber) {
                $type = $types[array_rand($types)];
                $monthly = match ($type) {
                    'Standard' => rand(1000000, 2000000),
                    'Deluxe' => rand(2000000, 3500000),
                    'VIP' => rand(3500000, 5000000),
                    default => rand(1000000, 5000000),
                };

                $kamar = Kamar::create([
                    'kos_id' => $kos->id,
                    'room_number' => $roomNumber,
                    'room_name' => 'Kamar '.$roomNumber,
                    'floor' => (int) substr($roomNumber, -2, 1),
                    'room_type' => $type,
                    'daily_price' => (int) ceil($monthly / 30),
                    'monthly_price' => $monthly,
                    'area' => $areas[array_rand($areas)],
                    'status' => 'available',
                ]);

                $facilityCount = match ($type) {
                    'Standard' => 3,
                    'Deluxe' => 5,
                    'VIP' => 7,
                    default => 4,
                };

                $kamar->fasilitas()->sync(
                    $allFacilities->random(min($facilityCount, $allFacilities->count()))->pluck('id')->toArray()
                );
            }
        }
    }
}
