<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index komposit untuk scheduler tagihan:auto-generate:
     * - kontraks(kos_id, status, start_date, end_date): filter kontrak aktif
     *   yang jatuh pada tanggal billing.
     */
    public function up(): void
    {
        Schema::table('kontraks', function (Blueprint $table) {
            $table->index(['kos_id', 'status', 'start_date', 'end_date'], 'kontraks_kos_status_period_index');
        });
    }

    public function down(): void
    {
        Schema::table('kontraks', function (Blueprint $table) {
            $table->dropIndex('kontraks_kos_status_period_index');
        });
    }
};
