<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payout_disbursements', function (Blueprint $table) {
            $table->string('idempotency_key', 64)->nullable()->after('payout_id');
            $table->enum('status', ['pending', 'verified', 'rejected', 'failed'])->default('pending')->after('amount');
            $table->timestamp('verified_at')->nullable()->after('transferred_at');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            $table->unique('idempotency_key', 'payout_disbursements_idempotency_unique');
            $table->index(['payout_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('payout_disbursements', function (Blueprint $table) {
            $table->dropUnique('payout_disbursements_idempotency_unique');
            $table->dropColumn(['idempotency_key', 'status', 'verified_at', 'verified_by']);
        });
    }
};
