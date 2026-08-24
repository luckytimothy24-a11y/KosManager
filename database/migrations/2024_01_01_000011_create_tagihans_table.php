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
        Schema::create('tagihans', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number', 20)->unique();
            $table->foreignId('penghuni_id')->constrained('penghunis')->onDelete('cascade');
            $table->foreignId('kontrak_id')->constrained('kontraks')->onDelete('cascade');
            $table->foreignId('kamar_id')->constrained('kamar')->onDelete('cascade');
            $table->string('bill_type');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('penalty', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->date('due_date');
            $table->enum('status', ['unpaid', 'pending_verification', 'paid', 'overdue', 'cancelled'])->default('unpaid');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihans');
    }
};
