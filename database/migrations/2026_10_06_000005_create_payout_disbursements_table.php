<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_disbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payout_id')->constrained('payouts')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('proof_path')->nullable();
            $table->timestamp('transferred_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['payout_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_disbursements');
    }
};
