<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Distribution record: pembayaran booking yang menjadi hak Owner.
     * Terpisah dari payment verification_status dan booking.status.
     *
     * platform_fee = 0 bila business rule existing tidak punya fee
     * (jangan hard-code persentase).
     */
    public function up(): void
    {
        Schema::create('payment_distributions', function (Blueprint $table) {
            $table->id();
            // 1 payment → maksimal 1 distribution (anti double payout)
            $table->foreignId('pembayaran_id')->unique()->constrained('pembayarans')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('platform_fee', 12, 2)->default(0);
            $table->decimal('owner_amount', 12, 2);
            // Status distribution — BUKAN payment status, BUKAN booking status.
            $table->enum('status', ['pending', 'ready', 'processed', 'failed'])->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_distributions');
    }
};
