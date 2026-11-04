<?php

namespace Database\Seeders;

use App\Models\MarketplaceItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class MarketplaceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = User::where('role', 'tenant')->first();
        if (!$tenant) {
            $this->command->warn('No tenant user found. Skipping marketplace seeding.');
            return;
        }

        $items = [
            [
                'title' => 'Kipas Angin Miyako 12 inch',
                'description' => 'Kipas angin second, masih berfungsi normal. Jarang dipakai karena kamar sudah ada AC. Harga nego tipis.',
                'price' => 150000,
                'condition' => 'bekas',
                'category' => 'elektronik',
                'image' => 'public/images/demo/kamar/kamar-desk.jpg',
            ],
            [
                'title' => 'Rak Buku Kayu 3 Tingkat',
                'description' => 'Rak buku kayu jati, kokoh. Sedikit goresan di bagian bawah tapi masih bagus. Tinggi 120cm.',
                'price' => 200000,
                'condition' => 'bekas',
                'category' => 'furniture',
                'image' => 'public/images/demo/kamar/kamar-wardrobe.jpg',
            ],
            [
                'title' => 'Buku Kalkulus Purcell Edisi 9',
                'description' => 'Buku kalkulus lengkap, kondisi 90%. Ada coretan pensil tipis di beberapa halaman. Cocok untuk mahasiswa teknik.',
                'price' => 75000,
                'condition' => 'bekas',
                'category' => 'buku',
                'image' => 'public/images/demo/kamar/kamar-standard-1.jpg',
            ],
            [
                'title' => 'Lampu Meja LED Adjustable',
                'description' => 'Lampu meja LED dengan 3 level brightness. Baru 3 bulan, masih garansi. Warna putih.',
                'price' => 120000,
                'condition' => 'baru',
                'category' => 'elektronik',
                'image' => 'public/images/demo/kamar/kamar-deluxe-1.jpg',
            ],
            [
                'title' => 'Kemeja Formal Pria L',
                'description' => 'Kemeja formal warna putih, size L. Dipakai 2x untuk seminar. Kondisi sangat baik.',
                'price' => 85000,
                'condition' => 'bekas',
                'category' => 'pakaian',
                'image' => 'public/images/demo/kamar/kamar-vip-1.jpg',
            ],
            [
                'title' => 'Meja Lipat Portable',
                'description' => 'Meja lipat portable, ringan dan mudah dibawa. Cocok untuk belajar atau makan. Tinggi adjustable.',
                'price' => 95000,
                'condition' => 'bekas',
                'category' => 'furniture',
                'image' => 'public/images/demo/kamar/kamar-standard-2.jpg',
            ],
            [
                'title' => 'Power Bank 10000mAh Xiaomi',
                'description' => 'Power bank Xiaomi 10000mAh, fast charging. Baterai masih bagus, bisa charge 2-3x HP. Ada kabel USB-C.',
                'price' => 180000,
                'condition' => 'bekas',
                'category' => 'elektronik',
                'image' => 'public/images/demo/kamar/kamar-deluxe-2.jpg',
            ],
            [
                'title' => 'Novel Laskar Pelangi',
                'description' => 'Novel Laskar Pelangi by Andrea Hirata. Kondisi baik, halaman lengkap. Cocok untuk koleksi.',
                'price' => 45000,
                'condition' => 'bekas',
                'category' => 'buku',
                'image' => 'public/images/demo/kamar/kamar-single.jpg',
            ],
        ];

        foreach ($items as $data) {
            $imagePath = null;
            $sourcePath = base_path($data['image']);
            if (file_exists($sourcePath)) {
                $ext = pathinfo($sourcePath, PATHINFO_EXTENSION);
                $filename = 'marketplace/' . uniqid() . '.' . $ext;
                Storage::disk('public')->put($filename, file_get_contents($sourcePath));
                $imagePath = $filename;
            }

            MarketplaceItem::create([
                'user_id' => $tenant->id,
                'kos_id' => $tenant->penghunis()->first()?->kos_id,
                'title' => $data['title'],
                'description' => $data['description'],
                'price' => $data['price'],
                'condition' => $data['condition'],
                'category' => $data['category'],
                'status' => 'available',
                'image_path' => $imagePath,
            ]);
        }

        $this->command->info('Marketplace demo items seeded: ' . count($items));
    }
}