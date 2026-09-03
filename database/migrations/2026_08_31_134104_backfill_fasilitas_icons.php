<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill missing icons on master fasilitas records using a consistent
     * RemixIcon mapping. Non-destructive: only updates rows when an icon is
     * absent, and only for names present in the mapping.
     */
    public function up(): void
    {
        $icons = [
            'AC' => 'ri-snowy-line',
            'Wifi' => 'ri-wifi-line',
            'WiFi' => 'ri-wifi-line',
            'Kipas' => 'ri-windy-line',
            'TV' => 'ri-tv-2-line',
            'Kasur' => 'ri-hotel-bed-line',
            'Lemari' => 'ri-archive-drawer-line',
            'Meja' => 'ri-table-line',
            'Meja Belajar' => 'ri-table-line',
            'Kursi' => 'ri-armchair-line',
            'Jendela' => 'ri-window-line',
            'Kamar Mandi Dalam' => 'ri-drop-line',
            'Water Heater' => 'ri-temperature-line',
            'Shower' => 'ri-showers-line',
            'Kloset Duduk' => 'ri-door-line',
            'Parkir' => 'ri-parking-line',
            'Parkir Motor' => 'ri-motorbike-line',
            'Parkir Mobil' => 'ri-car-line',
            'CCTV' => 'ri-cctv-line',
            'Dapur' => 'ri-restaurant-2-line',
            'Dapur Bersama' => 'ri-restaurant-2-line',
            'Ruang Tamu' => 'ri-sofa-line',
            'Laundry' => 'ri-laundry-line',
            'Mesin Cuci' => 'ri-washing-machine-line',
            'Kulkas' => 'ri-fridge-line',
            'Ruang Makan' => 'ri-restaurant-line',
            'Teras' => 'ri-plant-line',
            'Keamanan 24 Jam' => 'ri-shield-user-line',
            'Penjaga Kos' => 'ri-user-star-line',
            'Dispenser' => 'ri-cup-line',
            'Mushola' => 'ri-mosque-line',
            'Almari' => 'ri-archive-drawer-line',
        ];

        $rows = DB::table('fasilitas')->get();

        foreach ($rows as $row) {
            $icon = $icons[$row->name] ?? null;
            if ($icon === null) {
                continue;
            }

            $update = ['icon' => $icon];
            if (empty($row->icon)) {
                DB::table('fasilitas')->where('id', $row->id)->update($update);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Icons are cosmetic data; no schema change to revert.
    }
};
