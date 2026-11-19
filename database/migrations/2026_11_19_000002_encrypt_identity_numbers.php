<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Enkripsi nomor identitas (NIK) at-rest.
 *
 * - Menambah kolom `identity_number_encrypted` (TEXT) + `identity_number_hash`
 *   (SHA-256, untuk pencarian exact-match).
 * - Mengenkripsi nilai lama dan mengosongkan kolom plaintext.
 * - Menghapus kolom `identity_number` lama.
 *
 * Accessor `identity_number` pada model dipertahankan lewat trait
 * HasEncryptedIdentityNumber sehingga kode pemanggil tidak berubah.
 */
return new class extends Migration
{
    private const TABLES = ['users', 'penghunis', 'bookings', 'booking_occupants'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'identity_number')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->text('identity_number_encrypted')->nullable();
                $blueprint->string('identity_number_hash', 64)->nullable()->index();
            });

            DB::table($table)
                ->select('id', 'identity_number')
                ->whereNotNull('identity_number')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($table) {
                    foreach ($rows as $row) {
                        $plain = (string) $row->identity_number;

                        if ($plain === '') {
                            continue;
                        }

                        // Idempotent: lewati nilai yang sudah terenkripsi.
                        try {
                            Crypt::decryptString($plain);

                            continue;
                        } catch (Throwable $e) {
                            // Bukan ciphertext — lanjut enkripsi.
                        }

                        DB::table($table)->where('id', $row->id)->update([
                            'identity_number_encrypted' => Crypt::encryptString($plain),
                            'identity_number_hash' => hash('sha256', $plain),
                            'identity_number' => null,
                        ]);
                    }
                });

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('identity_number');
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'identity_number_encrypted')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('identity_number', 50)->nullable();
            });

            DB::table($table)
                ->select('id', 'identity_number_encrypted')
                ->whereNotNull('identity_number_encrypted')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($table) {
                    foreach ($rows as $row) {
                        try {
                            $plain = Crypt::decryptString((string) $row->identity_number_encrypted);
                        } catch (Throwable $e) {
                            $plain = (string) $row->identity_number_encrypted;
                        }

                        DB::table($table)->where('id', $row->id)->update([
                            'identity_number' => $plain,
                        ]);
                    }
                });

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn(['identity_number_encrypted', 'identity_number_hash']);
            });
        }
    }
};
