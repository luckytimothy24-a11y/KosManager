<?php

namespace Database\Seeders;

use App\Models\Kamar;
use App\Models\Kos;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Mengisi foto demo untuk Kos & Kamar menggunakan field existing (kos.photo, kamar.photo).
 *
 * Non-destruktif: hanya menyalin file foto dari public/images/demo ke disk 'public'
 * dan meng-update kolom photo milik kos/kamar demo yang dikenal berdasarkan nama/nomor kamar.
 * Tidak ada data lain yang disentuh. Aman dijalankan berulang kali (idempotent).
 */
class DemoPhotoSeeder extends Seeder
{
    /**
     * @var array<string, string> Nama kos => nama file foto di public/images/demo/kos
     */
    private const KOS_PHOTOS = [
        'Kos Melati' => 'kos-melati.jpg',
        'Kos Mawar' => 'kos-mawar.jpg',
        'Kos Kenanga' => 'kos-kenanga.jpg',
    ];

    /**
     * @var array<string, string> Nomor kamar => nama file foto di public/images/demo/kamar
     */
    private const KAMAR_PHOTOS = [
        // Kos Melati
        'A01' => 'kamar-deluxe-1.jpg',
        'A02' => 'kamar-vip-1.jpg',
        'A03' => 'kamar-standard-1.jpg',
        'A04' => 'kamar-single.jpg',
        'A05' => 'kamar-deluxe-2.jpg',
        // Kos Mawar
        'B01' => 'kamar-deluxe-2.jpg',
        'B02' => 'kamar-standard-2.jpg',
        'B03' => 'kamar-vip-2.jpg',
        'B04' => 'kamar-desk.jpg',
        'B05' => 'kamar-single.jpg',
        // Kos Kenanga
        'C01' => 'kamar-standard-2.jpg',
        'C02' => 'kamar-wardrobe.jpg',
        'C03' => 'kamar-vip-1.jpg',
        'C04' => 'kamar-deluxe-1.jpg',
        'C05' => 'kamar-single.jpg',
    ];

    public function run(): void
    {
        $kosCount = 0;
        $kamarCount = 0;

        foreach (self::KOS_PHOTOS as $name => $file) {
            $path = $this->copyToPublicDisk('images/demo/kos', 'kos', $file);
            if ($path === null) {
                continue;
            }
            $kosCount += Kos::where('name', $name)->update(['photo' => $path]);
        }

        foreach (self::KAMAR_PHOTOS as $roomNumber => $file) {
            $path = $this->copyToPublicDisk('images/demo/kamar', 'kamar', $file);
            if ($path === null) {
                continue;
            }
            $kamarCount += Kamar::where('room_number', $roomNumber)->update(['photo' => $path]);
        }

        $this->command?->info("DemoPhotoSeeder: {$kosCount} kos & {$kamarCount} kamar diberi foto.");
    }

    /**
     * Salin file demo ke disk 'public' bila belum ada. Return path relatif atau null jika sumber tidak ada.
     */
    private function copyToPublicDisk(string $sourceDir, string $targetDir, string $file): ?string
    {
        $source = public_path($sourceDir.'/'.$file);
        $target = $targetDir.'/'.$file;

        if (! is_file($source)) {
            $this->command?->warn("Skip: sumber tidak ditemukan ({$source})");

            return null;
        }

        if (! Storage::disk('public')->exists($target)) {
            Storage::disk('public')->put($target, file_get_contents($source));
        }

        return $target;
    }
}
