<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Payout/Settlement ke Pemilik Kos Asli.
     *
     * Alur: Owner menghitung kewajiban → transfer manual di luar aplikasi →
     * upload bukti → Super Admin verifikasi.
     *
     * TERPISAH dari `payment_distributions` (distribusi pembayaran booking →
     * hak role Owner/pengelola). Dua konsep berbeda — jangan digabung.
     *
     * platform_fee default 0 — MVP tidak menerima fee dari Owner
     * (tidak ada business rule fee existing; jangan hard-code %).
     * amount = hak Pemilik Kos Asli = gross_amount - platform_fee.
     *
     * Nominal dihitung SERVER-SIDE dari pembayarans approved
     * (tagihan chain + booking chain), filter payment_date periode.
     */
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->string('payout_number', 20)->unique(); // PO-XXXXXXXXXXXX
            $table->foreignId('kos_id')->constrained('kos')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete(); // pengelola pembuat
            // Pihak penerima — jangan hilang saat audit: restrict (bukan cascade).
            $table->foreignId('property_owner_id')->constrained('property_owners')->restrictOnDelete();

            $table->decimal('gross_amount', 12, 2);   // pendapatan eligible (server-side)
            $table->decimal('platform_fee', 12, 2)->default(0); // MVP: selalu 0, tidak editable Owner
            $table->decimal('amount', 12, 2);          // hak pemilik = gross - fee

            $table->date('period_start');
            $table->date('period_end');

            // pending → transferred (Owner upload bukti) → verified/rejected (Super Admin).
            // rejected → transferred (Owner resubmit record yang SAMA — bukan payout baru).
            $table->enum('status', ['pending', 'transferred', 'verified', 'rejected'])->default('pending');

            $table->timestamp('transferred_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('proof_path')->nullable(); // disk private, folder payout-proof
            $table->text('notes')->nullable();
            $table->timestamps();

            // Anti double payout per periode (DB backstop + lockForUpdate di service).
            $table->unique(['kos_id', 'period_start', 'period_end'], 'payouts_kos_period_unique');
            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
