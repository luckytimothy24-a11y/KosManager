<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Kos;
use App\Models\Kamar;
use App\Models\KosPhoto;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

Storage::disk('public')->makeDirectory('kos');

$owner = User::where('role','owner')->first();
if (!$owner) {
    $owner = User::factory()->create(['role'=>'owner','name'=>'Pak Owner Demo']);
    echo "Created owner {$owner->id}\n";
} else {
    echo "Using owner {$owner->id} - {$owner->name}\n";
}

$kos = Kos::create([
    'owner_id' => $owner->id,
    'name' => 'Kos Bersama Ceria - Kapasitas 4 Orang',
    'address' => 'Jl. Kaliurang KM 5, Sleman, Yogyakarta',
    'latitude' => -7.75,
    'longitude' => 110.37,
    'description' => 'Kos eksklusif untuk 4 orang per kamar, cocok untuk mahasiswa/peer group. Kamar luas 20m2, billing patungan (shared), bisa booking bareng teman. Tersedia dapur bersama, parkir luas, WiFi.',
    'phone' => '081234567890',
    'general_facilities' => 'WiFi;Dapur Bersama;Parkir Motor;Ruang Tamu',
    'rules' => 'Jam malam 23:00, tamu lapor pengelola',
    'payment_info' => 'BCA 1234567890 a.n. Kos Bersama Ceria',
    'status' => 'active',
]);

echo "Kos created id={$kos->id}\n";

$kamar = Kamar::create([
    'kos_id' => $kos->id,
    'room_number' => 'A-101',
    'room_name' => 'Kamar Shared 4 Orang',
    'floor' => 1,
    'room_type' => 'Shared',
    'capacity' => 4,
    'billing_mode' => 'shared',
    'daily_price' => 80000,
    'weekly_price' => 450000,
    'monthly_price' => 1500000,
    'area' => '20m2',
    'description' => 'Kamar shared 4 bed, lemari per orang, meja belajar, AC. Sisa slot bisa dilihat pencari kos.',
    'status' => 'available',
]);

echo "Kamar created id={$kamar->id} capacity={$kamar->capacity} billing={$kamar->billing_mode}\n";

// Buat 5 foto galeri dummy (1 cover + 4 detail)
$defs = [
    ['label'=>'cover - tampak depan kos','cover'=>true],
    ['label'=>'kamar - 4 bed','cover'=>false],
    ['label'=>'dapur bersama','cover'=>false],
    ['label'=>'kamar mandi dalam','cover'=>false],
    ['label'=>'parkir & ruang tamu','cover'=>false],
];

foreach ($defs as $idx => $d) {
    $path = "kos/{$kos->id}/".str_pad($idx,2,'0',STR_PAD_LEFT).".jpg";
    // buat file dummy 1x1 jpg minimal agar Storage::exists true
    // pakai 1x1 png base64
    $img = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII=');
    Storage::disk('public')->put($path, $img);
    $photo = KosPhoto::create([
        'kos_id' => $kos->id,
        'path' => $path,
        'position' => $idx,
        'is_cover' => $d['cover'],
    ]);
    echo "Photo {$photo->id} pos={$idx} cover=".($d['cover']?'1':'0')." path={$path} [{$d['label']}]\n";
}

echo "DONE kos={$kos->id} kamar={$kamar->id} photos=".KosPhoto::where('kos_id',$kos->id)->count()."\n";
echo "Cek: tenant/kos/{$kos->id} | owner/kos/{$kos->id}/edit\n";
