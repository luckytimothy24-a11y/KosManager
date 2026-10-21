<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Link Kos → Pemilik Kos Asli (property owner).
     *
     * Nullable: belum semua kos harus punya pemilik asli terdaftar.
     * Payout hanya bisa dibuat jika kolom ini terisi + rekening tujuan ada.
     * Pemilik asli di-resolve dari relasi ini (server-side) — bukan dari request.
     */
    public function up(): void
    {
        Schema::table('kos', function (Blueprint $table) {
            $table->foreignId('property_owner_id')
                ->nullable()
                ->after('owner_id')
                ->constrained('property_owners')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('property_owner_id');
        });
    }
};
