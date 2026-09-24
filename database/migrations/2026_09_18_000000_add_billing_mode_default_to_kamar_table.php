<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Set default billing_mode 'personal' untuk kamar yang belum ada.
     *
     * Migration ini memastikan semua kamar memiliki billing_mode yang jelas.
     * Karena migration 2026_09_12_000001 sudah menambahkan kolom dengan default,
     * migration ini hanya backfill data lama yang mungkin null.
     */
    public function up(): void
    {
        // Update semua kamar dengan billing_mode null atau kosong ke 'personal'
        DB::table('kamar')
            ->whereNull('billing_mode')
            ->orWhere('billing_mode', '')
            ->update(['billing_mode' => 'personal']);
    }

    public function down(): void
    {
        // Tidak bisa reverse karena default sudah di-schemaset
        // Tidak ada operasi destructive
    }
};
