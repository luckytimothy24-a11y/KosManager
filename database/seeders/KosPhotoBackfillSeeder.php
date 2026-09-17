<?php

namespace Database\Seeders;

use App\Models\Kos;
use App\Models\KosPhoto;
use Illuminate\Database\Seeder;

class KosPhotoBackfillSeeder extends Seeder
{
    public function run(): void
    {
        Kos::whereNotNull('photo')->cursor()->each(function (Kos $kos) {
            if ($kos->photos()->exists()) {
                return;
            }

            KosPhoto::create([
                'kos_id' => $kos->id,
                'path' => $kos->photo,
                'position' => 0,
                'is_cover' => true,
            ]);
        });
    }
}
