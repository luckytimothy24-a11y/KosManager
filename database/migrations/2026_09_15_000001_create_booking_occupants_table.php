<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_occupants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->unsignedTinyInteger('occupant_index');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('display_name')->nullable();
            $table->string('status')->default('invited');
            $table->string('invite_token_hash')->nullable()->unique();
            $table->timestamp('invite_expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->string('identity_status')->default('not_submitted');
            $table->string('identity_number', 50)->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->timestamp('identity_data_submitted_at')->nullable();
            $table->timestamp('identity_confirmed_at')->nullable();
            $table->foreignId('identity_confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('identity_rejected_at')->nullable();
            $table->text('identity_rejection_reason')->nullable();
            $table->foreignId('penghuni_id')->nullable()->constrained('penghunis')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['booking_id', 'occupant_index']);
            $table->index(['booking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_occupants');
    }
};
