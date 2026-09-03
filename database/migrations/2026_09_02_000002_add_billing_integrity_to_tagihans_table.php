<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * P8 Batch 2 — Billing Integrity.
 *
 * Menambahkan tombol billing deterministik (nullable + unique) untuk mencegah
 * duplikasi tagihan di level database, melengkapi cek app-level (exists() +
 * lockForUpdate pada baris kontrak). Pola ini sama dengan `active_payment_key`
 * pada tabel pembayaran (P8 Batch 1).
 *
 * Kompatibel MySQL / PostgreSQL / SQLite-test:
 * - kolom nullable, unique index standar (bukan partial/generated column);
 * - backfill dedup: hanya baris pertama (id terkecil) per (kontrak, periode)
 *   yang diberi kunci; duplikat historis dibiarkan NULL sehingga migrasi aman
 *   dijalankan tanpa menghapus/mengubah data lama.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->string('active_billing_key')->nullable()->after('status');
        });

        $this->backfillBillingKeys();

        Schema::table('tagihans', function (Blueprint $table) {
            $table->unique('active_billing_key', 'tagihans_active_billing_unique');
        });
    }

    public function down(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropUnique('tagihans_active_billing_unique');
            $table->dropColumn('active_billing_key');
        });
    }

    /**
     * Isi kunci billing untuk baris aktif (non soft-deleted). Untuk kombinasi
     * (kontrak, periode) yang terduplikasi, hanya row pertama yang diberi
     * kunci agar migrasi tetap lolos di data historis yang sudah ada.
     */
    private function backfillBillingKeys(): void
    {
        $table = 'tagihans';

        $rows = DB::table($table)
            ->whereNull('deleted_at')
            ->select(['id', 'kontrak_id', 'period_start', 'period_end'])
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            $isFirst = DB::table($table)
                ->whereNull('deleted_at')
                ->where('kontrak_id', $row->kontrak_id)
                ->where('period_start', $row->period_start)
                ->where('period_end', $row->period_end)
                ->where('id', '<', $row->id)
                ->doesntExist();

            if (! $isFirst) {
                continue;
            }

            DB::table($table)
                ->where('id', $row->id)
                ->update([
                    'active_billing_key' => $row->kontrak_id.':'.$row->period_start.':'.$row->period_end,
                ]);
        }
    }
};
