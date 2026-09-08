<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Entity PARTNER (mitra monetisasi) — entitas first-class di balik kampanye
     * iklan B2B (mis. DANA, Shopee, GoPay).
     *
     * Sebelumnya identitas advertiser pihak ketiga hanya tersimpan sebagai
     * string mentah di advertising_campaigns (advertiser_name). Kini partner
     * yang terdaftar menjadi referensi: dapat dipilih saat membuat campaign
     * dan dianalisis secara agregat (berapa banyak campaign per partner).
     *
     * PENTING: fitur ini MURNI monetisasi iklan. Sistem pembayaran sewa tenant
     * (pembayarans) TIDAK disentuh — cash-only tetap dipertahankan.
     */
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 130)->unique();
            $table->string('logo', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('website_url', 500)->nullable();
            // Kerangka kerja kemitraan / monetisasi (mis. CPM, CPC, deal, kontrak).
            $table->string('monetization_type', 50)->nullable();
            $table->string('contact_name', 120)->nullable();
            $table->string('contact_email', 190)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
