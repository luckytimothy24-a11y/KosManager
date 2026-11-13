<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('pembayaran_id')->nullable()->constrained('pembayarans')->nullOnDelete();
            $table->enum('type', ['escrow_in', 'commission', 'payout', 'refund', 'adjustment']);
            $table->bigInteger('amount'); // integer rupiah, can be negative
            $table->string('description')->nullable();
            $table->string('reference_number')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['owner_id', 'type']);
            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
