<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * P8 Batch 1 — integritas data pembayaran level database, aditif & portabel.
     *
     * 1. `active_payment_key` (nullable) + unique:
     *    Satu kolom biasa (bukan generated) berisi `tagihan_id` SAAT pembayaran aktif
     *    (status pending/approved) dan `NULL` saat rejected. Index unique pada kolom
     *    nullable memperbolehkan banyak NULL (riwayat rejected / non-active) sementara
     *    memaksakan satu baris non-NULL per tagihan. Portabel ke MySQL/PostgreSQL/SQLite
     *    (index unique pada kolom nullable, NULL selalu distinct).
     *
     * 2. Unique(gateway_reference, gateway_provider):
     *    Mencegah duplikasi referensi gateway pada provider yang sama. NULL distinct
     *    sehingga pembayaran manual (kedua kolom NULL) tidak terpengaruh.
     *
     * Kolom `active_payment_key` dipelihara oleh aplikasi di dalam transaksi yang sama
     * dengan siklus pembayaran (create -> pending, verify/reject). Index unique menjadi
     * backstop DB-level terhadap race condition / bug aplikasi.
     */
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->unsignedBigInteger('active_payment_key')
                ->nullable()
                ->after('verification_status');
        });

        // Backfill: pembayaran aktif yang sudah ada ikut diberi kunci agar index unique
        // konsisten. Kolom di-set SEBELUM index dibuat supaya data lama ter-capture.
        DB::table('pembayarans')
            ->whereIn('verification_status', ['pending', 'approved'])
            ->whereNull('active_payment_key')
            ->update(['active_payment_key' => DB::raw('tagihan_id')]);

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->unique('active_payment_key', 'pembayarans_active_payment_unique');

            $table->unique(
                ['gateway_reference', 'gateway_provider'],
                'pembayarans_gateway_ref_provider_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropUnique('pembayarans_gateway_ref_provider_unique');
            $table->dropUnique('pembayarans_active_payment_unique');
            $table->dropColumn('active_payment_key');
        });
    }
};
