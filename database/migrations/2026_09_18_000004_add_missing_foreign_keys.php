<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penghunis', function (Blueprint $table) {
            if (!Schema::hasColumn('penghunis', 'user_id')) {
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('penghunis', 'kos_id')) {
                $table->foreignId('kos_id')->constrained('kos')->onDelete('cascade');
            }
            if (!Schema::hasColumn('penghunis', 'kamar_id')) {
                $table->foreignId('kamar_id')->constrained('kamar')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penghunis', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['kos_id']);
            $table->dropForeign(['kamar_id']);
        });
    }
};
