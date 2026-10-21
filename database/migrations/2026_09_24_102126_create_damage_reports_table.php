<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('damage_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kos_id')->constrained('kos')->cascadeOnDelete();
            $table->foreignId('kamar_id')->nullable()->constrained('kamar')->nullOnDelete();
            $table->string('category');
            $table->text('description');
            $table->string('photos')->nullable();
            $table->string('status')->default('pending');
            $table->text('owner_reply')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_reports');
    }
};
