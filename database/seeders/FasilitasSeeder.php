<?php

namespace Database\Seeders;

use App\Models\Fasilitas;
use Illuminate\Database\Seeder;

class FasilitasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fasilitas = ['WiFi', 'AC', 'Kamar Mandi Dalam', 'Lemari', 'Kasur', 'Meja', 'Kursi', 'Parkir', 'CCTV', 'Water Heater'];

        foreach ($fasilitas as $name) {
            Fasilitas::create(['name' => $name]);
        }
    }
}
