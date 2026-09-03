<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan scope (type) dan metadata pada master fasilitas.
     * Non-destructive: kolom lama name/icon dipertahankan.
     */
    public function up(): void
    {
        Schema::table('fasilitas', function (Blueprint $table) {
            $table->enum('type', ['kos', 'kamar'])->default('kamar')->after('name');
            $table->text('description')->nullable()->after('icon');
            $table->boolean('is_active')->default(true)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fasilitas', function (Blueprint $table) {
            $table->dropColumn(['type', 'description', 'is_active']);
        });
    }
};
