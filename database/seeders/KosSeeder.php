<?php

namespace Database\Seeders;

use App\Models\Kos;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class KosSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', 'owner@example.com')->first();

        $kosData = [
            [
                'name' => 'Kost Nawasena Seturan',
                'address' => 'Jl. Seturan Raya No. 12, Seturan, Sleman',
                'description' => 'Kost putri eksklusif dekat UPN dan UII. Lingkungan aman, akses mudah ke bus Trans Jogja. Tersedia parkir mobil dan motor.',
                'phone' => '081234567890',
                'status' => 'active',
                'photo' => 'kos/kos-melati.jpg',
            ],
            [
                'name' => 'Kos Arunika Babarsari',
                'address' => 'Jl. Babarsari Raya No. 45, Babarsari, Depok, Sleman',
                'description' => 'Kost campuran modern strategis dekat kampus UNY dan STIE YKPN. Fasilitas lengkap: AC, Wi-Fi, laundry, dapur bersama.',
                'phone' => '081234567891',
                'status' => 'active',
                'photo' => 'kos/kos-mawar.jpg',
            ],
            [
                'name' => 'Kos Maleo Residence',
                'address' => 'Jl. Gejayan No. 78, Condongcatur, Depok, Sleman',
                'description' => 'Kost putra premium full furnished. Setiap kamar memiliki AC, lemari, meja belajar. Keamanan 24 jam dengan CCTV.',
                'phone' => '081234567892',
                'status' => 'active',
                'photo' => 'kos/kos-kenanga.jpg',
            ],
            [
                'name' => 'Kost Taman Gejayan',
                'address' => 'Jl. Affandi No. 33, Caturtunggal, Depok, Sleman',
                'description' => 'Kost campuran nyaman dengan taman asri. Cocok untuk mahasiswa dan pekerja. Dekat Gejayan Food Street dan kafe-kafe populer.',
                'phone' => '081234567893',
                'status' => 'active',
                'photo' => 'kos/kos-melati.jpg',
            ],
            [
                'name' => 'Rumah Kos Srikandi',
                'address' => 'Jl. Pogung Raya No. 56, Pogung Lor, Sinduadi, Sleman',
                'description' => 'Kost putri tradisional dengan nuansa rumah tinggal. Pemilik tinggal di tempat, cocok untuk yang mencari suasana kekeluargaan.',
                'phone' => '081234567894',
                'status' => 'active',
                'photo' => 'kos/kos-kenanga.jpg',
            ],
            [
                'name' => 'Kos Pondok Pogung',
                'address' => 'Jl. Ring Road Utara No. 91, Pogung Kidul, Sinduadi, Sleman',
                'description' => 'Kost campuran strategis di pinggir ring road. Akses cepat ke UGM, UPN, dan pusat perbelanjaan. Parkir luas.',
                'phone' => '081234567895',
                'status' => 'active',
                'photo' => 'kos/kos-mawar.jpg',
            ],
            [
                'name' => 'Kost Cendana Jakal',
                'address' => 'Jl. Kaliurang Km 5.5, No. 18, Jakal, Caturtunggal, Sleman',
                'description' => 'Kost putri asri di area Jakal. Suasana tenang, jauh dari keramaian namun tetap dekat kampus. Taman dan area jemur luas.',
                'phone' => '081234567896',
                'status' => 'active',
                'photo' => 'kos/kos-melati.jpg',
            ],
            [
                'name' => 'Kos Adikara Residence',
                'address' => 'Jl. Mangkubumen No. 105, Notoprajan, Ngupasan, Gondomanan, Yogyakarta',
                'description' => 'Kost premium dekat Malioboro dan kraton. Strategis untuk mahasiswa UGM, UNY, dan pekerja pusat kota. Fully furnished.',
                'phone' => '081234567897',
                'status' => 'active',
                'photo' => 'kos/kos-kenanga.jpg',
            ],
        ];

        foreach ($kosData as $data) {
            $kos = Kos::create(array_merge($data, ['owner_id' => $owner->id]));
            if (Storage::disk('public')->exists($data['photo'])) {
                $kos->update(['photo' => $data['photo']]);
            }
        }

        $admin = User::where('email', 'admin@example.com')->first();
        Kos::all()->each(fn (Kos $kos) => $kos->admins()->syncWithoutDetaching([$admin->id]));
    }
}

        $admin = User::where('email', 'admin@example.com')->first();
        Kos::all()->each(fn (Kos $kos) => $kos->admins()->syncWithoutDetaching([$admin->id]));
    }
}
