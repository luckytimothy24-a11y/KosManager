<?php

namespace App\Console\Commands;

use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Kos;
use Illuminate\Console\Command;

class SeedSharingRooms extends Command
{
    protected $signature = 'kamar:seed-sharing {--kos=} : Nama kos spesifik (opsional, pisahkan dengan koma)';

    protected $description = 'Tambah kamar sharing demo: Twin Sharing (kapasitas 2) + Family Room (kapasitas 4) per kos';

    private const TARGET_KOS = [
        'Kost Nawasena Seturan',
        'Kos Arunika Babarsari',
        'Kos Maleo Residence',
        'Kost Taman Gejayan',
        'Rumah Kos Srikandi',
        'Kos Pondok Pogung',
        'Kost Cendana Jakal',
        'Kos Adikara Residence',
        'Kos Melati',
        'Kos Mawar',
        'Kos Kenanga',
        'Kost Eksekutif Malioboro',
    ];

    private const ROOM_TEMPLATES = [
        [
            'room_number' => 'SH-01',
            'room_name' => 'Twin Sharing',
            'room_type' => 'Sharing',
            'capacity' => 2,
            'area' => '16m2',
            'photo' => 'kamar/kamar-standard-1.jpg',
            'price_ratio' => 0.75,
            'price_floor' => 500000,
        ],
        [
            'room_number' => 'SH-02',
            'room_name' => 'Family Room',
            'room_type' => 'Family',
            'capacity' => 4,
            'area' => '28m2',
            'photo' => 'kamar/kamar-vip-2.jpg',
            'price_ratio' => 1.10,
            'price_floor' => 1500000,
        ],
    ];

    public function handle(): int
    {
        $kosNames = $this->resolveKosNames();
        $allFacilities = Fasilitas::all();

        $created = 0;
        $skipped = 0;

        foreach ($kosNames as $kosName) {
            $kos = Kos::where('name', $kosName)->first();
            if (! $kos) {
                $this->warn("Kos tidak ditemukan: {$kosName}");

                continue;
            }

            $avgMonthly = $this->averageMonthlyPrice($kos->id);
            $maxFloor = $this->maxFloor($kos->id) ?? 1;

            foreach (self::ROOM_TEMPLATES as $template) {
                if (Kamar::where('kos_id', $kos->id)->where('room_number', $template['room_number'])->exists()) {
                    $this->line("  SKIP  {$kosName} — {$template['room_number']} sudah ada");
                    $skipped++;

                    continue;
                }

                $monthly = max(
                    $template['price_floor'],
                    (int) round($avgMonthly * $template['price_ratio'])
                );
                $weekly = (int) round($monthly * 0.26);
                $daily = (int) ceil($monthly / 30);

                $kamar = Kamar::create([
                    'kos_id' => $kos->id,
                    'room_number' => $template['room_number'],
                    'room_name' => $template['room_name'],
                    'room_type' => $template['room_type'],
                    'capacity' => $template['capacity'],
                    'billing_mode' => 'shared',
                    'floor' => $maxFloor + 1,
                    'daily_price' => $daily,
                    'weekly_price' => $weekly,
                    'monthly_price' => $monthly,
                    'area' => $template['area'],
                    'description' => "{$template['room_name']} untuk {$template['capacity']} orang. Biaya kamar dapat dibagi antar penghuni.",
                    'photo' => $template['photo'],
                    'status' => 'available',
                ]);

                $facilityCount = match ($template['room_type']) {
                    'Family' => min(6, $allFacilities->count()),
                    default => min(4, $allFacilities->count()),
                };

                if ($facilityCount > 0 && $allFacilities->count() > 0) {
                    $kamar->fasilitas()->sync(
                        $allFacilities->random($facilityCount)->pluck('id')->toArray()
                    );
                }

                $this->info("  + {$kosName} — {$template['room_number']} {$template['room_name']} (cap {$template['capacity']}, shared, Rp ".number_format($monthly).')');
                $created++;
            }
        }

        $this->newLine();
        $this->info("Selesai: {$created} kamar baru, {$skipped} dilewati.");

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function resolveKosNames(): array
    {
        $option = $this->option('kos');
        if (! $option) {
            return self::TARGET_KOS;
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $option))));
    }

    private function averageMonthlyPrice(int $kosId): float
    {
        $avg = Kamar::where('kos_id', $kosId)->avg('monthly_price');

        return $avg ? (float) $avg : 1000000;
    }

    private function maxFloor(int $kosId): ?int
    {
        $max = Kamar::where('kos_id', $kosId)->max('floor');

        return $max !== null ? (int) $max : null;
    }
}
