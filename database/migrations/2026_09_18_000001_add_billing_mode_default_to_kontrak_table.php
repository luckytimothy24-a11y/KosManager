<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Set default billing_mode 'personal' untuk kontrak yang belum ada.
     *
     * Migration ini memastikan semua kontrak memiliki billing_mode yang jelas
     * untuk konsistensi billing logic.
     */
    public function up(): void
    {
        // Update semua kontrak dengan billing_mode null ke 'personal'
        DB::table('kontraks')
            ->whereNull('billing_mode')
            ->update(['billing_mode' => 'personal']);
    }

    public function down(): void
    {
        // Tidak bisa reverse karena default sudah di-schemaset
    }
};
