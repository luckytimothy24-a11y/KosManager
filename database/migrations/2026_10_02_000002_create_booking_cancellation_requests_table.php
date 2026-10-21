<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_cancellation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            // Request status — BUKAN booking.status.
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
            $table->index(['status', 'reviewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_cancellation_requests');
    }
};
