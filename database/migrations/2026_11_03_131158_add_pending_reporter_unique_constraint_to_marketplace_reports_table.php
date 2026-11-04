<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support ALTER TABLE ... ADD COLUMN with GENERATED ALWAYS in older versions
        // Use raw SQL for maximum compatibility with both MySQL and SQLite
        if (DB::getDriverName() === 'sqlite') {
            // SQLite 3.31+ supports generated columns
            DB::statement("
                ALTER TABLE marketplace_reports 
                ADD COLUMN pending_reporter_id BIGINT 
                GENERATED ALWAYS AS (CASE WHEN status = 'pending' THEN reporter_id ELSE NULL END) STORED
            ");
            DB::statement("
                CREATE UNIQUE INDEX unique_pending_report_per_item 
                ON marketplace_reports (item_id, pending_reporter_id)
            ");
        } else {
            // MySQL 8.0+ supports generated columns
            Schema::table('marketplace_reports', function (Blueprint $table) {
                $table->unsignedBigInteger('pending_reporter_id')
                    ->nullable()
                    ->storedAs("CASE WHEN status = 'pending' THEN reporter_id ELSE NULL END")
                    ->after('status');
                $table->unique(['item_id', 'pending_reporter_id'], 'unique_pending_report_per_item');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("DROP INDEX IF EXISTS unique_pending_report_per_item");
            // SQLite doesn't support DROP COLUMN easily, but we can leave it or use workaround
            // For migration rollback, we'll just drop the index
        } else {
            Schema::table('marketplace_reports', function (Blueprint $table) {
                $table->dropUnique('unique_pending_report_per_item');
                $table->dropColumn('pending_reporter_id');
            });
        }
    }
};
