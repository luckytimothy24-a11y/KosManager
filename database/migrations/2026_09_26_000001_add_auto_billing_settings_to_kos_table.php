<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kos', function (Blueprint $table) {
            $table->boolean('auto_billing_enabled')->default(true)->after('status');
            $table->unsignedTinyInteger('billing_day')->default(1)->after('auto_billing_enabled');
            $table->unsignedTinyInteger('due_days')->default(5)->after('billing_day');
        });
    }

    public function down(): void
    {
        Schema::table('kos', function (Blueprint $table) {
            $table->dropColumn(['auto_billing_enabled', 'billing_day', 'due_days']);
        });
    }
};
