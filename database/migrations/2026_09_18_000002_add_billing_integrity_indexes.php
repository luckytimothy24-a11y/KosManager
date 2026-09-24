<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing indexes for billing integrity optimization.
     *
     * These indexes improve query performance for common billing operations.
     */
    public function up(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            // Index for billing integrity check (active_billing_key lookup)
            $table->index('active_billing_key');
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            // Index for payment integrity check (active_payment_key lookup)
            $table->index('active_payment_key');
        });
    }

    public function down(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropIndex('tagihans_active_billing_key_index');
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropIndex('pembayarans_active_payment_key_index');
        });
    }
};
