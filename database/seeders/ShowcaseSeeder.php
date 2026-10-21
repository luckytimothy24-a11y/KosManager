<?php

namespace Database\Seeders;

use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\KosPhoto;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Data showcase multi-penghuni untuk demo & uji kelayakan (DEV ONLY).
 *
 * - 3 kos baru (idempotent berdasarkan nama), ±14 kamar detail.
 * - Kamar sharing kapasitas 2–4 dengan billing_mode shared.
 * - Foto hanya dari public/images/demo/kos/* yang sudah ada di repo,
 *   disalin ke disk public mengikuti pola DemoPhotoSeeder.
 * - Fasilitas diambil dari FasilitasSeeder existing berdasarkan nama.
 *
 * Daftarkan HANYA di luar production (lihat DatabaseSeeder).
 */
class ShowcaseSeeder extends Seeder
{
    private const KOS_LIST = [
        [
            'name' => 'Griya Srikandi Pogung',
            'address' => 'Jl. Pogung Baru No. 27, Pogung Lor, Sinduadi, Sleman',
            'description' => 'Kost putri eksklusif dengan konsep homey. Menyediakan kamar single dan twin sharing untuk mahasiswi. Lingkungan tenang, pemilik tinggal di lokasi, keamanan 24 jam.',
            'phone' => '081234560001',
            'status' => 'active',
            'cover' => 'kos-fasad-2.jpg',
            'gallery' => ['kos-taman-1.jpg', 'kos-parkir-1.jpg'],
            'facilities' => ['WiFi', 'CCTV', 'Parkir Motor', 'Dapur Bersama', 'Laundry', 'Keamanan 24 Jam'],
        ],
        [
            'name' => 'Pondok Bareng Jakal',
            'address' => 'Jl. Kaliurang Km 6, No. 44, Caturtunggal, Depok, Sleman',
            'description' => 'Kost campuran ramah patungan: tersedia kamar triple sharing dan family room berkapasitas 4 orang dengan tagihan patungan otomatis per penghuni. Cocok untuk kelompok mahasiswa satu jurusan.',
            'phone' => '081234560002',
            'status' => 'active',
            'cover' => 'kos-fasad-3.jpg',
            'gallery' => ['kos-interior-1.jpg', 'kos-parkir-1.jpg'],
            'facilities' => ['WiFi', 'CCTV', 'Parkir Motor', 'Parkir Mobil', 'Dapur Bersama', 'Ruang Tamu', 'Area Jemur'],
        ],
        [
            'name' => 'Kost Eksekutif Malioboro',
            'address' => 'Jl. Sosrowijayan No. 8, Sosromenduran, Gedongtengen, Yogyakarta',
            'description' => 'Kost premium 5 menit jalan kaki dari Malioboro. Seluruh kamar deluxe single fully furnished dengan AC, water heater, dan smart lock. Prioritas pekerja dan mahasiswa pascasarjana.',
            'phone' => '081234560003',
            'status' => 'active',
            'cover' => 'kos-mewah-1.jpg',
            'gallery' => ['kos-interior-1.jpg', 'kos-fasad-1.jpg'],
            'facilities' => ['WiFi', 'CCTV', 'Parkir Motor', 'Laundry', 'Keamanan 24 Jam'],
        ],
    ];

    private const ROOMS = [
        'Griya Srikandi Pogung' => [
            ['number' => 'S-01', 'name' => 'Standard Single', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 950000, 'weekly' => 250000, 'area' => '12m2', 'floor' => 1, 'photo' => 'kamar-putih-1.jpg', 'facilities' => ['Kipas', 'Kasur']],
            ['number' => 'S-02', 'name' => 'Standard Single', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 950000, 'weekly' => 250000, 'area' => '12m2', 'floor' => 1, 'photo' => 'kamar-cozy-1.jpg', 'facilities' => ['Kipas', 'Kasur']],
            ['number' => 'S-03', 'name' => 'Twin Sharing', 'type' => 'Sharing', 'capacity' => 2, 'monthly' => 750000, 'weekly' => 195000, 'area' => '16m2', 'floor' => 1, 'photo' => 'kamar-kos-1.jpg', 'facilities' => ['AC', 'Kasur']],
            ['number' => 'S-04', 'name' => 'Twin Sharing', 'type' => 'Sharing', 'capacity' => 2, 'monthly' => 750000, 'weekly' => 195000, 'area' => '16m2', 'floor' => 2, 'photo' => 'kamar-modern-1.jpg', 'facilities' => ['AC', 'Kasur']],
            ['number' => 'S-05', 'name' => 'Standard Single', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 900000, 'weekly' => 235000, 'area' => '11m2', 'floor' => 2, 'photo' => 'kamar-atas-1.jpg', 'facilities' => ['Kipas', 'Kasur']],
            ['number' => 'S-06', 'name' => 'Standard Single', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 900000, 'weekly' => 235000, 'area' => '11m2', 'floor' => 2, 'photo' => 'kamar-atas-2.jpg', 'facilities' => ['Kipas', 'Kasur']],
        ],
        'Pondok Bareng Jakal' => [
            ['number' => 'B-01', 'name' => 'Standard Single', 'type' => 'Standard', 'capacity' => 1, 'monthly' => 800000, 'weekly' => 210000, 'area' => '10m2', 'floor' => 1, 'photo' => 'kamar-putih-1.jpg', 'facilities' => ['Kipas', 'Kasur']],
            ['number' => 'B-02', 'name' => 'Triple Sharing', 'type' => 'Sharing', 'capacity' => 3, 'monthly' => 600000, 'weekly' => 160000, 'area' => '20m2', 'floor' => 1, 'photo' => 'kamar-cozy-1.jpg', 'facilities' => ['AC', 'Kasur']],
            ['number' => 'B-03', 'name' => 'Triple Sharing', 'type' => 'Sharing', 'capacity' => 3, 'monthly' => 600000, 'weekly' => 160000, 'area' => '20m2', 'floor' => 2, 'photo' => 'kamar-modern-1.jpg', 'facilities' => ['AC', 'Kasur']],
            ['number' => 'B-04', 'name' => 'Family Room', 'type' => 'Family', 'capacity' => 4, 'monthly' => 1800000, 'weekly' => 470000, 'area' => '30m2', 'floor' => 2, 'photo' => 'kamar-mewah-1.jpg', 'facilities' => ['AC', 'Kasur']],
        ],
        'Kost Eksekutif Malioboro' => [
            ['number' => 'E-01', 'name' => 'Deluxe Single', 'type' => 'Deluxe', 'capacity' => 1, 'monthly' => 2200000, 'weekly' => 570000, 'area' => '18m2', 'floor' => 1, 'photo' => 'kamar-mewah-1.jpg', 'facilities' => ['AC', 'Kasur']],
            ['number' => 'E-02', 'name' => 'Deluxe Single', 'type' => 'Deluxe', 'capacity' => 1, 'monthly' => 2200000, 'weekly' => 570000, 'area' => '18m2', 'floor' => 2, 'photo' => 'kamar-cozy-1.jpg', 'facilities' => ['AC', 'Kasur']],
            ['number' => 'E-03', 'name' => 'Deluxe Single', 'type' => 'Deluxe', 'capacity' => 1, 'monthly' => 2200000, 'weekly' => 570000, 'area' => '20m2', 'floor' => 2, 'photo' => 'kamar-modern-1.jpg', 'facilities' => ['AC', 'Kasur']],
            ['number' => 'E-04', 'name' => 'Deluxe Single', 'type' => 'Deluxe', 'capacity' => 1, 'monthly' => 2200000, 'weekly' => 570000, 'area' => '20m2', 'floor' => 3, 'photo' => 'kamar-kos-1.jpg', 'facilities' => ['AC', 'Kasur']],
        ],
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('ShowcaseSeeder dilarang di environment production (data demo multi-penghuni).');
        }

        $owner = User::where('email', 'owner@example.com')->first()
            ?? User::where('role', 'owner')->first();
        abort_if(! $owner, 500, 'ShowcaseSeeder membutuhkan minimal satu user owner.');

        foreach (['kos', 'kamar'] as $dir) {
            if (! Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir);
            }
        }

        foreach (self::KOS_LIST as $data) {
            $kos = Kos::where('name', $data['name'])->first();
            if (! $kos) {
                $coverPath = $this->publishPhoto($data['cover'], 'kos');
                $kos = Kos::create([
                    'owner_id' => $owner->id,
                    'name' => $data['name'],
                    'address' => $data['address'],
                    'description' => $data['description'],
                    'phone' => $data['phone'],
                    'status' => $data['status'],
                    'photo' => $coverPath,
                ]);

                if ($coverPath) {
                    KosPhoto::updateOrCreate(
                        ['kos_id' => $kos->id, 'position' => 0],
                        ['path' => $coverPath, 'position' => 0, 'is_cover' => true]
                    );
                }

                foreach ($data['gallery'] as $position => $file) {
                    $path = $this->publishPhoto($file, 'kos');
                    if ($path) {
                        KosPhoto::updateOrCreate(
                            ['kos_id' => $kos->id, 'path' => $path],
                            ['path' => $path, 'position' => $position + 1, 'is_cover' => false]
                        );
                    }
                }

                $kosFacilityIds = Fasilitas::whereIn('name', $data['facilities'])->pluck('id')->all();
                if (! empty($kosFacilityIds)) {
                    $kos->fasilitas()->syncWithoutDetaching($kosFacilityIds);
                }

                $admin = User::where('email', 'admin@example.com')->first();
                if ($admin) {
                    $kos->admins()->syncWithoutDetaching([$admin->id]);
                }
            }

            foreach (self::ROOMS[$data['name']] as $room) {
                $exists = Kamar::where('kos_id', $kos->id)->where('room_number', $room['number'])->exists();
                if ($exists) {
                    continue;
                }

                $isSharing = ($room['type'] === 'Sharing' || ($room['capacity'] ?? 1) > 1);
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
                    'photo' => $this->publishPhoto($room['photo'], 'kamar'),
                ]);

                $roomFacilityIds = Fasilitas::whereIn('name', $room['facilities'])->pluck('id')->all();
                if (! empty($roomFacilityIds)) {
                    $kamar->fasilitas()->sync($roomFacilityIds);
                }
            }
        }
    }

    /**
     * Salin gambar demo lokal ke disk public bila tersedia.
     * Mengembalikan path storage relatif atau null bila sumber tidak ada
     * (tanpa generate/unduh — sesuai keputusan data).
     */
    private function publishPhoto(string $file, string $dir): ?string
    {
        $targetPath = $dir.'/'.$file;

        if (Storage::disk('public')->exists($targetPath)) {
            return $targetPath;
        }

        $sourcePath = public_path('images/demo/kos/'.$file);
        if (! is_file($sourcePath)) {
            return null;
        }

        Storage::disk('public')->put($targetPath, file_get_contents($sourcePath));

        return $targetPath;
    }
}
