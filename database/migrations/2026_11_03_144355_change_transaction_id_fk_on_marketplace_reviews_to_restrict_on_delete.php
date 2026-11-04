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
     * Step 3 & 4: Break transaction_id, reviewer_id, and reviewee_id cascades to restrictOnDelete
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            
            DB::statement('
                CREATE TABLE marketplace_reviews_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    transaction_id INTEGER NOT NULL,
                    reviewer_id INTEGER NOT NULL,
                    reviewee_id INTEGER NOT NULL,
                    rating TINYINT NOT NULL,
                    comment TEXT,
                    created_at DATETIME,
                    updated_at DATETIME,
                    UNIQUE (transaction_id, reviewer_id),
                    FOREIGN KEY (transaction_id) REFERENCES marketplace_transactions(id) ON DELETE RESTRICT,
                    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE RESTRICT,
                    FOREIGN KEY (reviewee_id) REFERENCES users(id) ON DELETE RESTRICT
                )
            ');
            
            DB::statement('
                INSERT INTO marketplace_reviews_new (id, transaction_id, reviewer_id, reviewee_id, rating, comment, created_at, updated_at)
                SELECT id, transaction_id, reviewer_id, reviewee_id, rating, comment, created_at, updated_at
                FROM marketplace_reviews
            ');
            
            DB::statement('DROP TABLE marketplace_reviews');
            DB::statement('ALTER TABLE marketplace_reviews_new RENAME TO marketplace_reviews');
            
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            Schema::table('marketplace_reviews', function (Blueprint $table) {
                $table->dropForeign(['transaction_id']);
                $table->dropForeign(['reviewer_id']);
                $table->dropForeign(['reviewee_id']);
                
                $table->foreignId('transaction_id')
                    ->constrained('marketplace_transactions')
                    ->restrictOnDelete();
                $table->foreignId('reviewer_id')
                    ->constrained('users')
                    ->restrictOnDelete();
                $table->foreignId('reviewee_id')
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
                CREATE TABLE marketplace_reviews_old (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    transaction_id INTEGER NOT NULL,
                    reviewer_id INTEGER NOT NULL,
                    reviewee_id INTEGER NOT NULL,
                    rating TINYINT NOT NULL,
                    comment TEXT,
                    created_at DATETIME,
                    updated_at DATETIME,
                    UNIQUE (transaction_id, reviewer_id),
                    FOREIGN KEY (transaction_id) REFERENCES marketplace_transactions(id) ON DELETE CASCADE,
                    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (reviewee_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ');
            
            DB::statement('
                INSERT INTO marketplace_reviews_old (id, transaction_id, reviewer_id, reviewee_id, rating, comment, created_at, updated_at)
                SELECT id, transaction_id, reviewer_id, reviewee_id, rating, comment, created_at, updated_at
                FROM marketplace_reviews
            ');
            
            DB::statement('DROP TABLE marketplace_reviews');
            DB::statement('ALTER TABLE marketplace_reviews_old RENAME TO marketplace_reviews');
            
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            Schema::table('marketplace_reviews', function (Blueprint $table) {
                $table->dropForeign(['transaction_id']);
                $table->dropForeign(['reviewer_id']);
                $table->dropForeign(['reviewee_id']);
                
                $table->foreignId('transaction_id')
                    ->constrained('marketplace_transactions')
                    ->cascadeOnDelete();
                $table->foreignId('reviewer_id')
                    ->constrained('users')
                    ->cascadeOnDelete();
                $table->foreignId('reviewee_id')
                    ->constrained('users')
                    ->cascadeOnDelete();
            });
        }
    }
};
