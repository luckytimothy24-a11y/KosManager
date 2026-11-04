<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_reviews', function (Blueprint $table) {
            // Add unique constraint on (transaction_id, reviewer_id)
            // to prevent one reviewer from creating multiple reviews for same transaction
            $table->unique(['transaction_id', 'reviewer_id'], 'marketplace_reviews_unique_transaction_reviewer');
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_reviews', function (Blueprint $table) {
            $table->dropUnique('marketplace_reviews_unique_transaction_reviewer');
        });
    }
};
