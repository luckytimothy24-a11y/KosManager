<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Traceability siapa yang menandai order advertising sebagai PAID (M2).
     *
     * Kolom nullable agar aman bagi data existing: order paid historis yang
     * dibuat sebelum kolom ini ada tetap mempertahankan paid_at tanpa actor
     * rekaan (paid_by NULL). Actor PASTI diisi server-side dari user yang
     * melakukan aksi (owner pay / moderator Tandai Terbayar), TIDAK pernah
     * dari input request.
     */
    public function up(): void
    {
        Schema::table('advertising_orders', function (Blueprint $table) {
            $table->foreignId('paid_by')->nullable()->after('paid_at')->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('advertising_orders', function (Blueprint $table) {
            $table->dropForeign(['paid_by']);
            $table->dropColumn('paid_by');
        });
    }
};
