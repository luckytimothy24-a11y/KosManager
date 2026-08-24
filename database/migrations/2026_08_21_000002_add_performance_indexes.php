<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index komposit untuk query panas:
     * - notifications(user_id, is_read): dihitung pada setiap page load (bel notifikasi).
     * - bookings(kamar_id, status, start_date, end_date): pengecekan konflik double booking.
     * - tagihans(kontrak_id, period_start, period_end): pengecekan duplicate billing.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read'], 'notifications_user_id_is_read_index');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['kamar_id', 'status', 'start_date', 'end_date'], 'bookings_kamar_status_period_index');
        });

        Schema::table('tagihans', function (Blueprint $table) {
            $table->index(['kontrak_id', 'period_start', 'period_end'], 'tagihans_kontrak_period_index');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_id_is_read_index');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_kamar_status_period_index');
        });

        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropIndex('tagihans_kontrak_period_index');
        });
    }
};
