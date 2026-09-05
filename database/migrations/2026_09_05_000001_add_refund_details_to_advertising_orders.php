<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Detail refund/credit untuk advertising_orders.
     *
     * Menutup GAP rekonsiliasi (F2): order yang sudah dibayar lalu kampanyenya
     * dibatalkan owner atau ditolak moderator kini MUTASI ke status 'refunded'
     * dan mencatat siapa/kapan/alasan pengembalian. Revenue (order paid) secara
     * otomatis mengecualikan order refunded; 'cancelled revenue' dapat dilacak.
     */
    public function up(): void
    {
        Schema::table('advertising_orders', function (Blueprint $table) {
            $table->dateTime('refunded_at')->nullable()->after('paid_at');
            $table->foreignId('refunded_by')->nullable()->after('refunded_at')->constrained('users')->onDelete('set null');
            $table->string('refund_reason', 190)->nullable()->after('refunded_by');
        });
    }

    public function down(): void
    {
        Schema::table('advertising_orders', function (Blueprint $table) {
            $table->dropForeign(['refunded_by']);
            $table->dropColumn(['refunded_at', 'refunded_by', 'refund_reason']);
        });
    }
};
