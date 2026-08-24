<?php

namespace Database\Seeders;

use App\Models\Kos;
use App\Models\User;
use Illuminate\Database\Seeder;

class KosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::where('email', 'owner@example.com')->first();

        Kos::create([
            'owner_id' => $owner->id,
            'name' => 'Kos Melati',
            'address' => 'Jl. Sudirman No. 10',
            'description' => 'Kos nyaman dan strategis di pusat kota.',
            'phone' => $owner->phone,
            'status' => 'active',
        ]);

        Kos::create([
            'owner_id' => $owner->id,
            'name' => 'Kos Mawar',
            'address' => 'Jl. Thamrin No. 25',
            'description' => 'Kos premium dengan fasilitas lengkap.',
            'phone' => $owner->phone,
            'status' => 'active',
        ]);

        Kos::create([
            'owner_id' => $owner->id,
            'name' => 'Kos Kenanga',
            'address' => 'Jl. Gatot Subroto No. 50',
            'description' => 'Kos murah meriah dekat kampus.',
            'phone' => $owner->phone,
            'status' => 'active',
        ]);

        // Tugaskan admin ke semua kos agar bisa mengelola booking, check-in, tagihan, dll.
        $admin = User::where('email', 'admin@example.com')->first();
        Kos::all()->each(fn (Kos $kos) => $kos->admins()->syncWithoutDetaching([$admin->id]));
    }
}
