<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tema ilustrasi visual untuk kartu promosi pihak ketiga.
     *
     * Nilai yang didukung: shopping, payment, wallet, generic.
     * Null = deteksi otomatis dari slug/nama mitra di model.
     * Kolom nullable agar seluruh data lama tetap aman tanpa backfill.
     */
    public function up(): void
    {
        Schema::table('advertising_campaigns', function (Blueprint $table) {
            $table->string('visual_theme', 32)->nullable()->after('highlight_text');
        });
    }

    public function down(): void
    {
        Schema::table('advertising_campaigns', function (Blueprint $table) {
            $table->dropColumn('visual_theme');
        });
    }
};
