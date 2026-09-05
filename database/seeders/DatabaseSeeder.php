<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            FasilitasSeeder::class,
            KosSeeder::class,
            KamarSeeder::class,
            AdvertisingSeeder::class,
            BookingSeeder::class,
            PenghuniSeeder::class,
            KontrakSeeder::class,
            TagihanSeeder::class,
            DemoPhotoSeeder::class,
        ]);
    }
}
