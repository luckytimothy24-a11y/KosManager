<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kontraks', function (Blueprint $table) {
            $table->string('billing_mode', 20)->nullable()->after('rental_price');
            $table->decimal('custom_share_amount', 12, 2)->nullable()->after('billing_mode');
        });
    }

    public function down(): void
    {
        Schema::table('kontraks', function (Blueprint $table) {
            $table->dropColumn(['billing_mode', 'custom_share_amount']);
        });
    }
};
