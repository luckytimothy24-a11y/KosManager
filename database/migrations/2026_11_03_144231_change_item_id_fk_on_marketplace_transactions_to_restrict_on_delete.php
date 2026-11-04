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
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            
            DB::statement('
                CREATE TABLE marketplace_transactions_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    item_id INTEGER NOT NULL,
                    buyer_id INTEGER NOT NULL,
                    seller_id INTEGER NOT NULL,
                    price DECIMAL(15,2) NOT NULL,
                    status VARCHAR(255) NOT NULL,
                    notes TEXT,
                    created_at DATETIME,
                    updated_at DATETIME,
                    FOREIGN KEY (item_id) REFERENCES marketplace_items(id) ON DELETE RESTRICT,
                    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ');
            
            DB::statement('
                INSERT INTO marketplace_transactions_new (id, item_id, buyer_id, seller_id, price, status, notes, created_at, updated_at)
                SELECT id, item_id, buyer_id, seller_id, price, status, notes, created_at, updated_at
                FROM marketplace_transactions
            ');
            
            DB::statement('DROP TABLE marketplace_transactions');
            DB::statement('ALTER TABLE marketplace_transactions_new RENAME TO marketplace_transactions');
            
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            Schema::table('marketplace_transactions', function (Blueprint $table) {
                $table->dropForeign(['item_id']);
                $table->foreignId('item_id')
                    ->constrained('marketplace_items')
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
                CREATE TABLE marketplace_transactions_old (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    item_id INTEGER NOT NULL,
                    buyer_id INTEGER NOT NULL,
                    seller_id INTEGER NOT NULL,
                    price DECIMAL(15,2) NOT NULL,
                    status VARCHAR(255) NOT NULL,
                    notes TEXT,
                    created_at DATETIME,
                    updated_at DATETIME,
                    FOREIGN KEY (item_id) REFERENCES marketplace_items(id) ON DELETE CASCADE,
                    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ');
            
            DB::statement('
                INSERT INTO marketplace_transactions_old (id, item_id, buyer_id, seller_id, price, status, notes, created_at, updated_at)
                SELECT id, item_id, buyer_id, seller_id, price, status, notes, created_at, updated_at
                FROM marketplace_transactions
            ');
            
            DB::statement('DROP TABLE marketplace_transactions');
            DB::statement('ALTER TABLE marketplace_transactions_old RENAME TO marketplace_transactions');
            
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            Schema::table('marketplace_transactions', function (Blueprint $table) {
                $table->dropForeign(['item_id']);
                $table->foreignId('item_id')
                    ->constrained('marketplace_items')
                    ->cascadeOnDelete();
            });
        }
    }
};
