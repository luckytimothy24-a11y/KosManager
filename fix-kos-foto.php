<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\Storage;
use App\Models\KosPhoto;

$kosId = 4;
$items = [
    ['bg'=>'#FF6B35','title'=>'Tampak Depan Kos','sub'=>'Kos Bersama Ceria — Cover'],
    ['bg'=>'#4A90E2','title'=>'Kamar 4 Bed Shared','sub'=>'20m² — Kapasitas 4 Orang'],
    ['bg'=>'#50C878','title'=>'Dapur Bersama','sub'=>'Kompor + Kulkas + Wastafel'],
    ['bg'=>'#9B59B6','title'=>'Kamar Mandi Dalam','sub'=>'Shower + Kloset Duduk'],
    ['bg'=>'#F39C12','title'=>'Parkir & Ruang Tamu','sub'=>'Parkir Luas + Sofa Santai'],
];

foreach ($items as $idx => $it) {
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600">
  <rect width="800" height="600" rx="24" fill="{$it['bg']}"/>
  <rect x="40" y="40" width="720" height="480" rx="18" fill="white" opacity="0.14"/>
  <text x="400" y="250" text-anchor="middle" font-family="Segoe UI, sans-serif" font-size="44" font-weight="800" fill="white">{$it['title']}</text>
  <text x="400" y="300" text-anchor="middle" font-family="Segoe UI, sans-serif" font-size="22" fill="white" opacity="0.92">{$it['sub']}</text>
  <text x="400" y="360" text-anchor="middle" font-family="Segoe UI, sans-serif" font-size="16" fill="white" opacity="0.8">Kos #{$kosId} — Foto {$idx} — 800×600</text>
</svg>
SVG;
    $path = "kos/{$kosId}/".str_pad($idx,2,'0',STR_PAD_LEFT).".svg";
    Storage::disk('public')->put($path, $svg);
    $photo = KosPhoto::where('kos_id',$kosId)->where('position',$idx)->first();
    if ($photo) {
        $old = $photo->path;
        // hapus file jpg lama jika beda ekstensi
        if ($old !== $path && Storage::disk('public')->exists($old)) Storage::disk('public')->delete($old);
        $photo->update(['path'=>$path]);
        echo "updated pos {$idx} {$old} -> {$path}\n";
    } else {
        KosPhoto::create(['kos_id'=>$kosId,'path'=>$path,'position'=>$idx,'is_cover'=> $idx===0]);
        echo "created pos {$idx} {$path}\n";
    }
}
echo "DONE\n";
foreach (KosPhoto::where('kos_id',$kosId)->orderBy('position')->get() as $p) {
    echo "  #{$p->id} pos={$p->position} cover=".($p->is_cover?'Y':'N')." {$p->path} exists=". (Storage::disk('public')->exists($p->path)?'yes':'no')."\n";
}
