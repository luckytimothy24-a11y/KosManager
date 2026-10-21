<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rekening tujuan payout milik Pemilik Kos Asli.
     *
     * TERPISAH dari `owner_bank_accounts` (rekening Owner/pengelola — tujuan
     * pembayaran tenant). Jangan digabung: pihak penerima berbeda.
     *
     * account_number string — leading zero aman. 1:1 per property owner
     * (unique) — konsisten pola owner_bank_accounts untuk MVP.
     */
    public function up(): void
    {
        Schema::create('property_owner_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_owner_id')->unique()->constrained('property_owners')->cascadeOnDelete();
            $table->string('bank_name');
            $table->string('account_number'); // string — leading zero aman
            $table->string('account_holder_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_owner_bank_accounts');
    }
};
