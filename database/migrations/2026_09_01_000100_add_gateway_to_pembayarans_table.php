<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom pembayaran online (gateway) — aditif, hanya menambah kolom.
     */
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->string('gateway_provider', 50)->nullable()->after('proof_file');
            $table->string('gateway_reference', 64)->nullable()->after('gateway_provider');
            $table->text('gateway_instructions')->nullable()->after('gateway_reference');
            $table->timestamp('gateway_expires_at')->nullable()->after('gateway_instructions');
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn([
                'gateway_provider',
                'gateway_reference',
                'gateway_instructions',
                'gateway_expires_at',
            ]);
        });
    }
};
