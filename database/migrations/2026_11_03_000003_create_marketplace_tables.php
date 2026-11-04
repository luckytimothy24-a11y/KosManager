<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kos_id')->nullable()->constrained('kos')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->string('condition')->default('bekas'); // baru, bekas, rusak_ringan
            $table->string('category')->default('lainnya'); // elektronik, furniture, pakaian, buku, lainnya
            $table->string('status')->default('available'); // available, reserved, sold
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        Schema::create('marketplace_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('marketplace_items')->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('price', 15, 2);
            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_transactions');
        Schema::dropIfExists('marketplace_items');
    }
};