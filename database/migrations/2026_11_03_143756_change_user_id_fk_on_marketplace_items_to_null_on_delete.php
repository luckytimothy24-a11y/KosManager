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
        // Drop the existing foreign key constraint (cascadeOnDelete)
        // Laravel's default FK name pattern: table_column_foreign
        if (DB::getDriverName() === 'sqlite') {
            // SQLite: foreign keys are not named explicitly in CREATE TABLE
            // We need to recreate the table to change FK behavior
            DB::statement('PRAGMA foreign_keys = OFF');
            
            // Create new table with nullOnDelete
            DB::statement('
                CREATE TABLE marketplace_items_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    kos_id INTEGER,
                    title VARCHAR(255) NOT NULL,
                    description TEXT,
                    price DECIMAL(15,2) NOT NULL,
                    condition VARCHAR(255) NOT NULL DEFAULT "bekas",
                    category VARCHAR(255) NOT NULL DEFAULT "lainnya",
                    status VARCHAR(255) NOT NULL DEFAULT "available",
                    image_path VARCHAR(255),
                    created_at DATETIME,
                    updated_at DATETIME,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                    FOREIGN KEY (kos_id) REFERENCES kos(id) ON DELETE SET NULL
                )
            ');
            
            // Copy data
            DB::statement('
                INSERT INTO marketplace_items_new (id, user_id, kos_id, title, description, price, condition, category, status, image_path, created_at, updated_at)
                SELECT id, user_id, kos_id, title, description, price, condition, category, status, image_path, created_at, updated_at
                FROM marketplace_items
            ');
            
            // Drop old table and rename
            DB::statement('DROP TABLE marketplace_items');
            DB::statement('ALTER TABLE marketplace_items_new RENAME TO marketplace_items');
            
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            // MySQL: drop and recreate FK with SET NULL
            Schema::table('marketplace_items', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->foreignId('user_id')
                    ->nullable()
                    ->change()
                    ->constrained()
                    ->nullOnDelete();
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
                CREATE TABLE marketplace_items_old (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    kos_id INTEGER,
                    title VARCHAR(255) NOT NULL,
                    description TEXT,
                    price DECIMAL(15,2) NOT NULL,
                    condition VARCHAR(255) NOT NULL DEFAULT "bekas",
                    category VARCHAR(255) NOT NULL DEFAULT "lainnya",
                    status VARCHAR(255) NOT NULL DEFAULT "available",
                    image_path VARCHAR(255),
                    created_at DATETIME,
                    updated_at DATETIME,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (kos_id) REFERENCES kos(id) ON DELETE SET NULL
                )
            ');
            
            DB::statement('
                INSERT INTO marketplace_items_old (id, user_id, kos_id, title, description, price, condition, category, status, image_path, created_at, updated_at)
                SELECT id, user_id, kos_id, title, description, price, condition, category, status, image_path, created_at, updated_at
                FROM marketplace_items
            ');
            
            DB::statement('DROP TABLE marketplace_items');
            DB::statement('ALTER TABLE marketplace_items_old RENAME TO marketplace_items');
            
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            Schema::table('marketplace_items', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->foreignId('user_id')
                    ->nullable(false)
                    ->change()
                    ->constrained()
                    ->cascadeOnDelete();
            });
        }
    }
};
