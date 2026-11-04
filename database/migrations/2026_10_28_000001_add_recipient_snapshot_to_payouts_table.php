<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Preserve the payout recipient selected at creation time.
     *
     * Existing payouts deliberately remain NULL: their historical recipient
     * cannot be reconstructed safely from a mutable bank-account profile.
     */
    public function up(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->foreignId('recipient_bank_account_id')
                ->nullable()
                ->after('property_owner_id')
                ->constrained('property_owner_bank_accounts')
                ->nullOnDelete();
            $table->string('recipient_bank_name')->nullable()->after('recipient_bank_account_id');
            $table->string('recipient_account_number')->nullable()->after('recipient_bank_name');
            $table->string('recipient_account_holder_name')->nullable()->after('recipient_account_number');
        });
    }

    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropForeign(['recipient_bank_account_id']);
            $table->dropColumn([
                'recipient_bank_account_id',
                'recipient_bank_name',
                'recipient_account_number',
                'recipient_account_holder_name',
            ]);
        });
    }
};
