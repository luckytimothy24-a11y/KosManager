<?php

namespace Database\Seeders;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\KosPhoto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DemoPhotoSeeder extends Seeder
{
    private const KOS_NAMES = [
        'Kost Nawasena Seturan',
        'Kos Arunika Babarsari',
        'Kos Maleo Residence',
        'Kost Taman Gejayan',
        'Rumah Kos Srikandi',
        'Kos Pondok Pogung',
        'Kost Cendana Jakal',
        'Kos Adikara Residence',
    ];

    private const KOS_COVER_MAP = [
        'Kost Nawasena Seturan' => 'kos-melati.jpg',
        'Kos Arunika Babarsari' => 'kos-mawar.jpg',
        'Kos Maleo Residence' => 'kos-kenanga.jpg',
        'Kost Taman Gejayan' => 'kos-mawar.jpg',
        'Rumah Kos Srikandi' => 'kos-melati.jpg',
        'Kos Pondok Pogung' => 'kos-kenanga.jpg',
        'Kost Cendana Jakal' => 'kos-mawar.jpg',
        'Kos Adikara Residence' => 'kos-melati.jpg',
    ];

    private const KOS_GALLERY_MAP = [
        'Kost Nawasena Seturan' => ['kamar-standard-1.jpg', 'kamar-standard-2.jpg', 'kamar-deluxe-1.jpg', 'kamar-desk.jpg', 'kamar-wardrobe.jpg'],
        'Kos Arunika Babarsari' => ['kamar-deluxe-2.jpg', 'kamar-standard-1.jpg', 'kamar-vip-2.jpg', 'kamar-desk.jpg', 'kamar-wardrobe.jpg'],
        'Kos Maleo Residence' => ['kamar-vip-1.jpg', 'kamar-deluxe-1.jpg', 'kamar-standard-2.jpg', 'kamar-wardrobe.jpg'],
        'Kost Taman Gejayan' => ['kamar-wardrobe.jpg', 'kamar-standard-2.jpg', 'kamar-single.jpg', 'kamar-deluxe-2.jpg'],
        'Rumah Kos Srikandi' => ['kamar-standard-2.jpg', 'kamar-single.jpg', 'kamar-desk.jpg', 'kamar-vip-2.jpg'],
        'Kos Pondok Pogung' => ['kamar-vip-1.jpg', 'kamar-deluxe-2.jpg', 'kamar-standard-1.jpg', 'kamar-wardrobe.jpg', 'kamar-desk.jpg'],
        'Kost Cendana Jakal' => ['kamar-standard-2.jpg', 'kamar-vip-2.jpg', 'kamar-single.jpg', 'kamar-wardrobe.jpg'],
        'Kos Adikara Residence' => ['kamar-deluxe-1.jpg', 'kamar-vip-2.jpg', 'kamar-standard-1.jpg', 'kamar-desk.jpg', 'kamar-wardrobe.jpg'],
    ];

    private const KAMAR_ROOM_PHOTOS = [
        'Kost Nawasena Seturan' => ['A-01' => 'kamar-single.jpg', 'A-02' => 'kamar-standard-1.jpg', 'A-03' => 'kamar-standard-2.jpg', 'B-01' => 'kamar-deluxe-1.jpg', 'B-02' => 'kamar-deluxe-2.jpg', 'B-03' => 'kamar-single.jpg'],
        'Kos Arunika Babarsari' => ['101' => 'kamar-standard-1.jpg', '102' => 'kamar-standard-2.jpg', '103' => 'kamar-standard-2.jpg', '201' => 'kamar-deluxe-1.jpg', '202' => 'kamar-desk.jpg', '203' => 'kamar-vip-1.jpg', '204' => 'kamar-deluxe-2.jpg'],
        'Kos Maleo Residence' => ['M-01' => 'kamar-single.jpg', 'M-02' => 'kamar-standard-1.jpg', 'M-03' => 'kamar-deluxe-2.jpg', 'M-04' => 'kamar-wardrobe.jpg', 'M-05' => 'kamar-vip-2.jpg', 'M-06' => 'kamar-standard-2.jpg'],
        'Kost Taman Gejayan' => ['TG-01' => 'kamar-standard-1.jpg', 'TG-02' => 'kamar-standard-2.jpg', 'TG-03' => 'kamar-standard-2.jpg', 'TG-04' => 'kamar-deluxe-1.jpg', 'TG-05' => 'kamar-vip-1.jpg', 'TG-06' => 'kamar-standard-1.jpg'],
        'Rumah Kos Srikandi' => ['SR-01' => 'kamar-single.jpg', 'SR-02' => 'kamar-standard-1.jpg', 'SR-03' => 'kamar-standard-1.jpg', 'SR-04' => 'kamar-standard-2.jpg', 'SR-05' => 'kamar-vip-2.jpg'],
        'Kos Pondok Pogung' => ['P-01' => 'kamar-standard-1.jpg', 'P-02' => 'kamar-standard-2.jpg', 'P-03' => 'kamar-standard-2.jpg', 'P-04' => 'kamar-deluxe-1.jpg', 'P-05' => 'kamar-desk.jpg', 'P-06' => 'kamar-vip-1.jpg', 'P-07' => 'kamar-standard-1.jpg', 'P-08' => 'kamar-vip-2.jpg'],
        'Kost Cendana Jakal' => ['CJ-01' => 'kamar-single.jpg', 'CJ-02' => 'kamar-standard-1.jpg', 'CJ-03' => 'kamar-standard-2.jpg', 'CJ-04' => 'kamar-deluxe-2.jpg', 'CJ-05' => 'kamar-wardrobe.jpg'],
        'Kos Adikara Residence' => ['AD-01' => 'kamar-single.jpg', 'AD-02' => 'kamar-standard-1.jpg', 'AD-03' => 'kamar-deluxe-1.jpg', 'AD-04' => 'kamar-standard-2.jpg', 'AD-05' => 'kamar-vip-1.jpg', 'AD-06' => 'kamar-vip-2.jpg', 'AD-07' => 'kamar-standard-2.jpg', 'AD-08' => 'kamar-standard-1.jpg'],
    ];

    public function run(): void
    {
        $this->ensureStorageDirectories();

        $this->seedKosCoverPhotos();
        $this->seedKamarPhotos();
        $this->seedKosGalleryPhotos();
        $this->seedKosPhotosForKosWithoutGallery();
    }

    private function ensureStorageDirectories(): void
    {
        foreach (['kos', 'kamar', 'kos/gallery', 'kos/4'] as $dir) {
            if (! Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir);
            }
        }
    }

    private const KOS_SVG_GALLERY_MAP = [
        'Kost Nawasena Seturan' => ['00.svg', '01.svg', '02.svg', '03.svg', '04.svg'],
        'Kos Arunika Babarsari' => ['04.svg'],
        'Kos Maleo Residence' => ['00.svg', '01.svg', '02.svg'],
        'Kost Taman Gejayan' => ['01.svg', '02.svg', '03.svg'],
        'Rumah Kos Srikandi' => ['03.svg'],
        'Kos Pondok Pogung' => ['04.svg', '00.svg'],
        'Kost Cendana Jakal' => ['00.svg', '01.svg'],
        'Kos Adikara Residence' => ['01.svg', '02.svg', '03.svg', '04.svg'],
    ];

    public function run(): void
    {
        $this->ensureStorageDirectories();

        $this->seedKosCoverPhotos();
        $this->seedKamarPhotos();
        $this->seedKosGalleryPhotos();
        $this->seedKosSVGGalleryPhotos();
        $this->seedKosPhotosForKosWithoutGallery();
    }

    private function seedKosSVGGalleryPhotos(): void
    {
        $sourceDir = storage_path('app/public/kos/4');
        if (! is_dir($sourceDir)) {
            return;
        }

        foreach (self::KOS_SVG_GALLERY_MAP as $kosName => $svgFiles) {
            $kos = Kos::where('name', $kosName)->first();
            if (! $kos) {
                continue;
            }

            foreach ($svgFiles as $position => $file) {
                $sourcePath = $sourceDir . '/' . $file;
                $targetPath = 'kos/4/' . $file;

                if (! Storage::disk('public')->exists($targetPath) && is_file($sourcePath)) {
                    Storage::disk('public')->copy($sourcePath, $targetPath);
                }

                if (Storage::disk('public')->exists($targetPath)) {
                    KosPhoto::updateOrCreate(
                        ['kos_id' => $kos->id, 'path' => $targetPath],
                        ['path' => $targetPath, 'position' => $position + 10, 'is_cover' => false]
                    );
                }
            }
        }
    }

    private function ensureSourcePhotosExist(): void
    {
        $sourceDir = public_path('images/demo/kamar');
        if (! is_dir($sourceDir)) {
            @mkdir($sourceDir, 0755, true);
        }

        $availablePhotos = [
            'kamar-single.jpg', 'kamar-standard-1.jpg', 'kamar-standard-2.jpg',
            'kamar-deluxe-1.jpg', 'kamar-deluxe-2.jpg', 'kamar-vip-1.jpg',
            'kamar-vip-2.jpg', 'kamar-desk.jpg', 'kamar-wardrobe.jpg',
        ];

        foreach ($availablePhotos as $file) {
            $target = $sourceDir . '/' . $file;
            if (! is_file($target) || filesize($target) < 100) {
                $this->generatePlaceholderImage($target, $file);
            }
        }
    }

    private function generatePlaceholderImage(string $path, string $filename): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            return;
        }

        $colors = [
            'kamar-single.jpg' => [139, 119, 101],
            'kamar-standard-1.jpg' => [100, 149, 237],
            'kamar-standard-2.jpg' => [70, 130, 180],
            'kamar-deluxe-1.jpg' => [218, 165, 32],
            'kamar-deluxe-2.jpg' => [184, 134, 11],
            'kamar-vip-1.jpg' => [178, 34, 34],
            'kamar-vip-2.jpg' => [139, 0, 0],
            'kamar-desk.jpg' => [60, 179, 113],
            'kamar-wardrobe.jpg' => [106, 90, 205],
        ];

        $color = $colors[$filename] ?? [128, 128, 128];
        $width = 800;
        $height = 600;
        $img = imagecreatetruecolor($width, $height);
        $bg = imagecolorallocate($img, $color[0], $color[1], $color[2]);
        imagefilledrectangle($img, 0, 0, $width, $height, $bg);

        $label = str_replace(['.jpg', 'kamar-'], '', $filename);
        $label = ucwords(str_replace('-', ' ', $label));

        if (is_file('C:/Windows/Fonts/arial.ttf')) {
            $fontFile = 'C:/Windows/Fonts/arial.ttf';
            imagettftext($img, 36, 0, $width / 2 - 120, $height / 2 - 20, imagecolorallocate($img, 255, 255, 255), $fontFile, $label);
            imagettftext($img, 18, 0, $width / 2 - 80, $height / 2 + 30, imagecolorallocate($img, 255, 255, 255), $fontFile, 'KosManager Demo');
        } else {
            imagestring($img, 5, $width / 2 - 60, $height / 2 - 15, $label, imagecolorallocate($img, 255, 255, 255));
        }

        imagejpeg($img, $path, 85);
        imagedestroy($img);
    }

    private function seedKosCoverPhotos(): void
    {
        foreach (self::KOS_NAMES as $kosName) {
            $kos = Kos::where('name', $kosName)->first();
            if (! $kos) {
                continue;
            }

            $file = self::KOS_COVER_MAP[$kosName] ?? 'kos-melati.jpg';
            $sourcePath = public_path('images/demo/kos/' . $file);

            if (Storage::disk('public')->exists('kos/' . $file)) {
                $existingPath = 'kos/' . $file;
                if (! $kos->photo || $kos->photo !== $existingPath) {
                    $kos->update(['photo' => $existingPath]);
                }

                $coverExists = KosPhoto::where('kos_id', $kos->id)->where('is_cover', true)->exists();
                if (! $coverExists) {
                    KosPhoto::updateOrCreate(
                        ['kos_id' => $kos->id, 'position' => 0],
                        ['path' => $existingPath, 'position' => 0, 'is_cover' => true]
                    );
                }
                continue;
            }

            if (is_file($sourcePath)) {
                $targetPath = 'kos/' . $file;
                if (! Storage::disk('public')->exists($targetPath)) {
                    Storage::disk('public')->put($targetPath, file_get_contents($sourcePath));
                }
                $kos->update(['photo' => $targetPath]);

                KosPhoto::updateOrCreate(
                    ['kos_id' => $kos->id, 'position' => 0],
                    ['path' => $targetPath, 'position' => 0, 'is_cover' => true]
                );
            }
        }
    }

    private function seedKamarPhotos(): void
    {
        foreach (self::KAMAR_ROOM_PHOTOS as $kosName => $rooms) {
            $kos = Kos::where('name', $kosName)->first();
            if (! $kos) {
                continue;
            }

            foreach ($rooms as $roomNumber => $file) {
                $kamar = Kamar::where('kos_id', $kos->id)->where('room_number', $roomNumber)->first();
                if (! $kamar) {
                    continue;
                }

                $sourcePath = public_path('images/demo/kamar/' . $file);

                if (Storage::disk('public')->exists('kamar/' . $file)) {
                    $existingPath = 'kamar/' . $file;
                    if (! $kamar->photo || $kamar->photo !== $existingPath) {
                        $kamar->update(['photo' => $existingPath]);
                    }
                    continue;
                }

                if (is_file($sourcePath)) {
                    $targetPath = 'kamar/' . $file;
                    if (! Storage::disk('public')->exists($targetPath)) {
                        Storage::disk('public')->put($targetPath, file_get_contents($sourcePath));
                    }
                    $kamar->update(['photo' => $targetPath]);
                }
            }
        }
    }

    private function seedKosGalleryPhotos(): void
    {
        $this->ensureSourcePhotosExist();

        foreach (self::KOS_GALLERY_MAP as $kosName => $files) {
            $kos = Kos::where('name', $kosName)->first();
            if (! $kos) {
                continue;
            }

            foreach ($files as $position => $file) {
                $sourcePath = public_path('images/demo/kamar/' . $file);
                $targetPath = 'kos/gallery/' . $kos->id . '/' . $file;

                if (! Storage::disk('public')->exists($targetPath) && is_file($sourcePath)) {
                    Storage::disk('public')->put($targetPath, file_get_contents($sourcePath));
                }

                if (Storage::disk('public')->exists($targetPath)) {
                    KosPhoto::updateOrCreate(
                        ['kos_id' => $kos->id, 'position' => $position],
                        ['path' => $targetPath, 'position' => $position, 'is_cover' => $position === 0]
                    );
                }
            }
        }
    }

    private function seedKosPhotosForKosWithoutGallery(): void
    {
        foreach (self::KOS_NAMES as $kosName) {
            $kos = Kos::where('name', $kosName)->first();
            if (! $kos) {
                continue;
            }

            $existingPhotos = KosPhoto::where('kos_id', $kos->id)->count();
            if ($existingPhotos >= 3) {
                continue;
            }

            $galleryFiles = self::KOS_GALLERY_MAP[$kosName] ?? [];
            foreach ($galleryFiles as $position => $file) {
                $targetPath = 'kos/gallery/' . $kos->id . '/' . $file;
                if (Storage::disk('public')->exists($targetPath)) {
                    continue;
                }

                $sourcePath = public_path('images/demo/kamar/' . $file);
                if (is_file($sourcePath)) {
                    Storage::disk('public')->put($targetPath, file_get_contents($sourcePath));
                }

                if (Storage::disk('public')->exists($targetPath)) {
                    KosPhoto::updateOrCreate(
                        ['kos_id' => $kos->id, 'position' => $position],
                        ['path' => $targetPath, 'position' => $position, 'is_cover' => $position === 0]
                    );
                }
            }
        }
    }
}
