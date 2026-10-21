<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pemilik Kos Asli (property owner) — pihak yang benar-benar memiliki
     * properti dan berhak menerima hasil pendapatan (settlement/payout).
     *
     * BERBEDA dari role `owner` (pengelola/partner/perantara di platform).
     * Entity ringan (bukan user platform) — tidak mengubah definisi role.
     */
    public function up(): void
    {
        Schema::create('property_owners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_owners');
    }
};
