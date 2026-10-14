<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payout_id')->nullable()->constrained('payouts')->nullOnDelete();
            $table->foreignId('pembayaran_id')->constrained('pembayarans')->restrictOnDelete();
            $table->enum('type', ['refund', 'correction']);
            $table->decimal('amount', 12, 2);
            $table->decimal('commission_delta', 12, 2)->default(0);
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['pembayaran_id']);
            $table->index(['payout_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_adjustments');
    }
};
