<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Owner percakapan disimpan eksplisit per pesan.
     *
     * Sebelumnya identitas owner diturunkan dari `kos.owner_id`. Akibatnya
     * riwayat percakapan ikut berpindah bila kepemilikan kos berubah dan
     * thread tenant/owner tidak punya identitas yang stabil. Dengan kolom ini,
     * setiap pesan menyimpan owner yang menjadi lawan bicara saat pesan dibuat.
     *
     * NULL = percakapan non-owner (mis. marketplace antar-tenant).
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('owner_id')
                ->nullable()
                ->after('kos_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['owner_id', 'kos_id'], 'messages_owner_id_kos_id_index');
        });

        $this->backfillOwnerIdFromKos();
    }

    /**
     * Backfill kompatibel SQLite & MySQL, dibatasi per chunk agar aman
     * untuk tabel messages yang sudah besar.
     */
    private function backfillOwnerIdFromKos(): void
    {
        DB::table('messages')
            ->select('id', 'kos_id')
            ->whereNull('owner_id')
            ->orderBy('id')
            ->chunkById(500, function ($messages) {
                $kosOwners = DB::table('kos')
                    ->whereIn('id', collect($messages)->pluck('kos_id')->filter()->unique()->values())
                    ->pluck('owner_id', 'id');

                $idsByOwner = [];

                foreach ($messages as $message) {
                    $ownerId = $kosOwners[$message->kos_id] ?? null;

                    if ($ownerId !== null) {
                        $idsByOwner[(int) $ownerId][] = $message->id;
                    }
                }

                foreach ($idsByOwner as $ownerId => $ids) {
                    DB::table('messages')
                        ->whereIn('id', $ids)
                        ->update(['owner_id' => $ownerId]);
                }
            });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropForeign(['owner_id']);
            });
        }

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_owner_id_kos_id_index');
            $table->dropColumn('owner_id');
        });
    }
};
