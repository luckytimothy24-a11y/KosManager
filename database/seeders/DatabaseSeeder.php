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
        if (app()->environment('production')) {
            $this->command?->warn('AdvertisingSeeder dilewati: production tidak boleh membuat data demo finansial (order PAID AD-DEMO-*).');
        }

        $this->call($this->seeders());
    }

    /**
     * Daftar seeder yang dijalankan.
     *
     * AdvertisingSeeder (data demo finansial: order PAID AD-DEMO-*) dikecualikan
     * pada environment production agar `php artisan db:seed` tidak pernah
     * menggelembungkan angka revenue produksi.
     *
     * @return array<class-string>
     */
    public function seeders(): array
    {
        $seeders = [
            UserSeeder::class,
            FasilitasSeeder::class,
            KosSeeder::class,
            KamarSeeder::class,
            BookingSeeder::class,
            PenghuniSeeder::class,
            KontrakSeeder::class,
            TagihanSeeder::class,
            DemoPhotoSeeder::class,
        ];

        if (! app()->environment('production')) {
            array_splice($seeders, 4, 0, [AdvertisingSeeder::class]);
        }

        return $seeders;
    }
}
