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
        Schema::create('check_outs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penghuni_id')->constrained('penghunis')->onDelete('cascade');
            $table->foreignId('kamar_id')->constrained('kamar')->onDelete('cascade');
            $table->date('request_date');
            $table->date('check_out_date')->nullable();
            $table->string('room_condition')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_outs');
    }
};
