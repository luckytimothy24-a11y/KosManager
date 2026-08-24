<?php

namespace Database\Seeders;

use App\Models\Kontrak;
use App\Models\Tagihan;
use Illuminate\Database\Seeder;

class TagihanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kontraks = Kontrak::where('status', 'active')->get();

        foreach ($kontraks as $index => $kontrak) {
            Tagihan::create([
                'bill_number' => 'TG'.str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                'penghuni_id' => $kontrak->penghuni_id,
                'kontrak_id' => $kontrak->id,
                'kamar_id' => $kontrak->kamar_id,
                'bill_type' => 'rent',
                'period_start' => now()->subMonth(),
                'period_end' => now(),
                'subtotal' => $kontrak->rental_price,
                'discount' => 0,
                'penalty' => 0,
                'total' => $kontrak->rental_price,
                'due_date' => now()->addWeek(),
                'status' => 'unpaid',
            ]);
        }
    }
}
