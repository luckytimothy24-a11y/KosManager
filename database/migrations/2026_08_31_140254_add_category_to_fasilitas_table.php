<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kategori fasilitas (room/bathroom/common/parking/security/service)
     * pada master fasilitas. Non-destructive: kolom lama name/icon/type dipertahankan,
     * nilai category di-backfill otomatis dari nama fasilitas yang sudah ada.
     */
    public function up(): void
    {
        Schema::table('fasilitas', function (Blueprint $table) {
            $table->string('category')->nullable()->after('type');
        });

        $categories = [
            'room' => ['AC', 'Kipas', 'TV', 'Kasur', 'Lemari', 'Almari', 'Meja', 'Meja Belajar', 'Kursi', 'Jendela', 'WiFi', 'Ventilasi', 'Balkon'],
            'bathroom' => ['Kamar Mandi Dalam', 'Kamar Mandi Luar', 'Shower', 'Water Heater', 'Air Panas', 'Kloset Duduk', 'Kloset Jongkok', 'Wastafel', 'Bak Mandi'],
            'common' => ['Dapur', 'Dapur Bersama', 'Ruang Makan', 'Ruang Tamu', 'Ruang Keluarga', 'Ruang Santai', 'Ruang Jemur', 'Area Jemur', 'Taman', 'Mushola', 'Dispenser', 'Kulkas', 'Mesin Cuci', 'TV Bersama', 'Teras'],
            'parking' => ['Parkir', 'Parkir Motor', 'Parkir Mobil', 'Parkir Sepeda', 'Garasi'],
            'security' => ['CCTV', 'Penjaga Kos', 'Keamanan 24 Jam', 'Security', 'Akses 24 Jam', 'Smart Lock'],
            'service' => ['Laundry', 'Cleaning Service', 'Housekeeping', 'Maintenance'],
        ];

        $rows = DB::table('fasilitas')->get();

        foreach ($rows as $row) {
            $category = null;

            foreach ($categories as $key => $names) {
                if (in_array($row->name, $names, true)) {
                    $category = $key;
                    break;
                }
            }

            if ($category !== null) {
                DB::table('fasilitas')
                    ->where('id', $row->id)
                    ->update(['category' => $category]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fasilitas', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
