<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('bank_name');
            $table->string('account_number'); // string — leading zero aman
            $table->string('account_holder_name');
            $table->timestamps();

            $table->unique('owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_bank_accounts');
    }
};
