<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill billing_mode untuk kontrak yang null
        \DB::table('kontraks')
            ->whereNull('billing_mode')
            ->update(['billing_mode' => 'personal']);
    }

    public function down(): void
    {
        // No reverse operation
    }
};
