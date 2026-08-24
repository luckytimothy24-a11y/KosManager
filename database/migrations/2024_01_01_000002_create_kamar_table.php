<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kamar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kos_id')->constrained('kos')->onDelete('cascade');
            $table->string('room_number');
            $table->string('room_name')->nullable();
            $table->integer('floor')->nullable();
            $table->string('room_type')->nullable();
            $table->decimal('daily_price', 12, 2);
            $table->decimal('monthly_price', 12, 2);
            $table->string('area')->nullable();
            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->enum('status', ['available', 'booked', 'occupied', 'maintenance'])->default('available');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kamar');
    }
};
