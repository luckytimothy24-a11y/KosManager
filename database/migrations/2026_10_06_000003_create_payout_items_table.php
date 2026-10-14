<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payout_id')->constrained('payouts')->cascadeOnDelete();
            $table->foreignId('pembayaran_id')->constrained('pembayarans')->restrictOnDelete();
            $table->decimal('gross_rent', 12, 2);
            $table->decimal('penalty_excluded', 12, 2)->default(0);
            $table->enum('commission_type', ['percent', 'fixed'])->nullable();
            $table->decimal('commission_rate', 12, 2)->nullable();
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2);
            $table->timestamps();

            $table->unique('pembayaran_id', 'payout_items_pembayaran_unique');
            $table->index(['payout_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_items');
    }
};
