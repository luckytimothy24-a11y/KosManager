<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Backfill: 1 kamar = 1 penghuni (patungan/multi dihapus).
     * Kontrak/tagihan shared yang sedang berjalan TIDAK diubah.
     */
    public function up(): void
    {
        if (! Schema::hasTable('kamar')) {
            return;
        }

        // Kamar lama shared/capacity>1 → personal capacity=1
        DB::table('kamar')
            ->where(function ($q) {
                $q->where('capacity', '>', 1)
                    ->orWhere('billing_mode', 'shared');
            })
            ->update([
                'capacity' => 1,
                'billing_mode' => 'personal',
            ]);

        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'billing_mode')) {
            DB::table('bookings')
                ->where('billing_mode', 'shared')
                ->orWhere('requested_occupants', '>', 1)
                ->update([
                    'billing_mode' => 'personal',
                    'requested_occupants' => 1,
                ]);
        }
    }

    public function down(): void
    {
        // Tidak mengembalikan data shared lama (patungan sudah dihapus dari product).
    }
};
