<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NULL = booking legacy tanpa DP (tetap memakai harga booking penuh).
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('down_payment_amount', 12, 2)->nullable();
        });

        // Snapshot DP di kontrak. Nilai > 0 = DP belum dipakai pada tagihan pertama.
        Schema::table('kontraks', function (Blueprint $table) {
            $table->decimal('down_payment_amount', 12, 2)->default(0);
        });

        // Nilai DP yang benar-benar dipotongkan pada tagihan (di-cap subtotal).
        Schema::table('tagihans', function (Blueprint $table) {
            $table->decimal('down_payment_discount', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropColumn('down_payment_discount');
        });

        Schema::table('kontraks', function (Blueprint $table) {
            $table->dropColumn('down_payment_amount');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('down_payment_amount');
        });
    }
};
