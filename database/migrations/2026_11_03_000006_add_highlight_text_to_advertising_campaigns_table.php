<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Teks penawaran besar (opsional) untuk visual iklan partner.
     *
     * Contoh: "Cashback 20%", "Gratis Ongkir". Nilai ini berasal dari admin /
     * advertiser — TIDAK di-hardcode di view. Bila kosong, tampilan memakai
     * fallback nama mitra sehingga data lama tetap aman.
     */
    public function up(): void
    {
        Schema::table('advertising_campaigns', function (Blueprint $table) {
            $table->string('highlight_text', 120)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('advertising_campaigns', function (Blueprint $table) {
            $table->dropColumn('highlight_text');
        });
    }
};
