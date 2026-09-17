<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kos_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kos_id')->constrained('kos')->cascadeOnDelete();
            $table->string('path', 255);
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->timestamps();

            $table->index(['kos_id', 'position']);
            $table->index(['kos_id', 'is_cover']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kos_photos');
    }
};
