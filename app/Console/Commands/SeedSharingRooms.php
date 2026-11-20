<?php

namespace App\Console\Commands;

use App\Models\Kamar;
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
        $this->warn('Command dinonaktifkan: kebijakan saat ini adalah satu kamar = satu penghuni (tanpa shared/multi-occupant).');

        return self::FAILURE;
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
