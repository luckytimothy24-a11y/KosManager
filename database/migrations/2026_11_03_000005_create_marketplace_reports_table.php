<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('marketplace_items')->cascadeOnDelete();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason'); // spam, penipuan, tidak_sesuai, lainnya
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, reviewed, dismissed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_reports');
    }
};