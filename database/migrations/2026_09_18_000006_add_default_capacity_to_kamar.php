<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('kamar')
            ->whereNull('capacity')
            ->update(['capacity' => 1]);
    }

    public function down(): void
    {
        // No reverse operation needed
    }
};
