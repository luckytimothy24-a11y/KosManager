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
        Schema::create('kontraks', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number', 20)->unique();
            $table->foreignId('penghuni_id')->constrained('penghunis')->onDelete('cascade');
            $table->foreignId('kos_id')->constrained('kos')->onDelete('cascade');
            $table->foreignId('kamar_id')->constrained('kamar')->onDelete('cascade');
            $table->enum('rental_type', ['daily', 'monthly']);
            $table->decimal('rental_price', 12, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'expired', 'terminated'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontraks');
    }
};
