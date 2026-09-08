<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hubungkan kampanye iklan ke ENTITAS PARTNER (mitra monetisasi) + metadata
     * kontrak, tanpa mengubah sistem pembayaran sewa tenant (cash-only tetap).
     *
     * Kolom baru SEMUA nullable agar data advertising yang sudah ada tetap
     * valid (bukan migrasi destruktif). partner_id diisi belakangan hanya untuk
     * kampanye yang memang terkait partner terdaftar.
     */
    public function up(): void
    {
        Schema::table('advertising_campaigns', function (Blueprint $table) {
            $table->foreignId('partner_id')->nullable()->after('kos_id')
                ->constrained('partners')->nullOnDelete();
            // Metadata kontrak/monetisasi kemitraan.
            $table->string('contract_reference', 120)->nullable()->after('placement');
            $table->string('campaign_code', 120)->nullable()->after('contract_reference');
            $table->string('monetization_type', 50)->nullable()->after('campaign_code');
            $table->string('target_audience', 190)->nullable()->after('monetization_type');
        });
    }

    public function down(): void
    {
        Schema::table('advertising_campaigns', function (Blueprint $table) {
            $table->dropForeign(['partner_id']);
            $table->dropColumn([
                'partner_id',
                'contract_reference',
                'campaign_code',
                'monetization_type',
                'target_audience',
            ]);
        });
    }
};
