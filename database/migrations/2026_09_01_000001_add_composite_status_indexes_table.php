<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indeks komposit tambahan (aditif, hanya index) untuk query hot-path
     * yang memfilter status oleh penghuni/kos.
     */
    public function up(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->index(['penghuni_id', 'status']);
        });

        Schema::table('check_outs', function (Blueprint $table) {
            $table->index(['penghuni_id', 'status']);
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->index(['penghuni_id', 'verification_status']);
        });
    }

    public function down(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropIndex(['penghuni_id', 'status']);
        });

        Schema::table('check_outs', function (Blueprint $table) {
            $table->dropIndex(['penghuni_id', 'status']);
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropIndex(['penghuni_id', 'verification_status']);
        });
    }
};
