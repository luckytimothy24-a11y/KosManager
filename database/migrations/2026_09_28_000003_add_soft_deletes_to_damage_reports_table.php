<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Soft-delete untuk damage_reports agar tenant dapat menarik laporan
 * miliknya yang masih pending tanpa menghilangkan jejak audit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('damage_reports', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('damage_reports', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
