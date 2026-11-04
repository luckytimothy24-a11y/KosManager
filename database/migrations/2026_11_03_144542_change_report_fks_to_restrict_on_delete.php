<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Step 5: Break report FKs (item_id and reporter_id) to restrictOnDelete
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            
            DB::statement('
                CREATE TABLE marketplace_reports_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    item_id INTEGER NOT NULL,
                    reporter_id INTEGER NOT NULL,
                    reason VARCHAR(255) NOT NULL,
                    description TEXT,
                    status VARCHAR(255) NOT NULL DEFAULT "pending",
                    pending_reporter_id BIGINT GENERATED ALWAYS AS (CASE WHEN status = "pending" THEN reporter_id ELSE NULL END) STORED,
                    created_at DATETIME,
                    updated_at DATETIME,
                    UNIQUE (item_id, pending_reporter_id),
                    FOREIGN KEY (item_id) REFERENCES marketplace_items(id) ON DELETE RESTRICT,
                    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE RESTRICT
                )
            ');
            
            DB::statement('
                INSERT INTO marketplace_reports_new (id, item_id, reporter_id, reason, description, status, created_at, updated_at)
                SELECT id, item_id, reporter_id, reason, description, status, created_at, updated_at
                FROM marketplace_reports
            ');
            
            DB::statement('DROP TABLE marketplace_reports');
            DB::statement('ALTER TABLE marketplace_reports_new RENAME TO marketplace_reports');
            
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            Schema::table('marketplace_reports', function (Blueprint $table) {
                $table->dropForeign(['item_id']);
                $table->dropForeign(['reporter_id']);
                
                $table->foreignId('item_id')
                    ->constrained('marketplace_items')
                    ->restrictOnDelete();
                $table->foreignId('reporter_id')
                    ->constrained('users')
                    ->restrictOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            
            DB::statement('
                CREATE TABLE marketplace_reports_old (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    item_id INTEGER NOT NULL,
                    reporter_id INTEGER NOT NULL,
                    reason VARCHAR(255) NOT NULL,
                    description TEXT,
                    status VARCHAR(255) NOT NULL DEFAULT "pending",
                    pending_reporter_id BIGINT GENERATED ALWAYS AS (CASE WHEN status = "pending" THEN reporter_id ELSE NULL END) STORED,
                    created_at DATETIME,
                    updated_at DATETIME,
                    UNIQUE (item_id, pending_reporter_id),
                    FOREIGN KEY (item_id) REFERENCES marketplace_items(id) ON DELETE CASCADE,
                    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ');
            
            DB::statement('
                INSERT INTO marketplace_reports_old (id, item_id, reporter_id, reason, description, status, created_at, updated_at)
                SELECT id, item_id, reporter_id, reason, description, status, created_at, updated_at
                FROM marketplace_reports
            ');
            
            DB::statement('DROP TABLE marketplace_reports');
            DB::statement('ALTER TABLE marketplace_reports_old RENAME TO marketplace_reports');
            
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            Schema::table('marketplace_reports', function (Blueprint $table) {
                $table->dropForeign(['item_id']);
                $table->dropForeign(['reporter_id']);
                
                $table->foreignId('item_id')
                    ->constrained('marketplace_items')
                    ->cascadeOnDelete();
                $table->foreignId('reporter_id')
                    ->constrained('users')
                    ->cascadeOnDelete();
            });
        }
    }
};
