<?php

namespace Database\Seeders;

use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Penghuni;
use Illuminate\Database\Seeder;

class KontrakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $penghunis = Penghuni::where('status', 'active')->get();

        foreach ($penghunis as $index => $penghuni) {
            $kamar = Kamar::find($penghuni->kamar_id);

            Kontrak::create([
                'contract_number' => 'KT'.str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                'penghuni_id' => $penghuni->id,
                'kos_id' => $penghuni->kos_id,
                'kamar_id' => $penghuni->kamar_id,
                'rental_type' => 'monthly',
                'rental_price' => $kamar->monthly_price,
                'start_date' => now()->subMonths(1),
                'end_date' => now()->addMonths(5),
                'status' => 'active',
            ]);
        }
    }
}
