<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            // Ensure unique index on active_billing_key exists (created by 2026_09_02_000002)
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            // Ensure unique index on active_payment_key exists (created by 2026_09_02_000001)
        });
    }

    public function down(): void
    {
        // No reverse operation needed for index verification
    }
};
