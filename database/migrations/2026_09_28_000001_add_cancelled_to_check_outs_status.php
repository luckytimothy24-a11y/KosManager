<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan status 'cancelled' pada check_outs.status.
 *
 * Justifikasi audit: capability cancel membutuhkan terminal state yang
 * berbeda dari 'rejected' (permintaan ditolak pengelola) — cancel =
 * permintaan ditarik sebelum diproses. Enum existing hanya memiliki
 * pending/approved/rejected/completed sehingga perubahan schema ini
 * benar-benar diperlukan dan minimal (satu nilai enum, tanpa kolom baru).
 *
 * MySQL/PostgreSQL memakai ALTER; SQLite menegakkan enum melalui CHECK
 * constraint sehingga tabel dibangun ulang (data disalin utuh).
 */
return new class extends Migration
{
    private const COLUMNS = [
        'id', 'penghuni_id', 'kamar_id', 'request_date', 'check_out_date',
        'room_condition', 'notes', 'status', 'verified_by', 'created_at', 'updated_at',
    ];

    /** @return string[] */
    private function statuses(bool $withCancelled): array
    {
        return $withCancelled
            ? ['pending', 'approved', 'rejected', 'completed', 'cancelled']
            : ['pending', 'approved', 'rejected', 'completed'];
    }

    private function blueprint(Blueprint $table, bool $withCancelled): void
    {
        $table->id();
        $table->foreignId('penghuni_id')->constrained('penghunis')->onDelete('cascade');
        $table->foreignId('kamar_id')->constrained('kamar')->onDelete('cascade');
        $table->date('request_date');
        $table->date('check_out_date')->nullable();
        $table->string('room_condition')->nullable();
        $table->text('notes')->nullable();
        $table->enum('status', $this->statuses($withCancelled))->default('pending');
        $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
        $table->timestamps();
    }

    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE check_outs MODIFY status ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE check_outs ALTER COLUMN status TYPE VARCHAR(20)');
        } elseif ($driver === 'sqlite') {
            Schema::create('check_outs_new', fn (Blueprint $table) => $this->blueprint($table, true));
            DB::table('check_outs_new')->insertUsing(self::COLUMNS, DB::table('check_outs')->select(self::COLUMNS));
            Schema::drop('check_outs');
            Schema::rename('check_outs_new', 'check_outs');
        }
        // sqlsrv: tidak ada enforce enum — tidak ada perubahan.
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql' && Schema::hasTable('check_outs')) {
            if (DB::table('check_outs')->where('status', 'cancelled')->count() === 0) {
                DB::statement("ALTER TABLE check_outs MODIFY status ENUM('pending', 'approved', 'rejected', 'completed') NOT NULL DEFAULT 'pending'");
            }
        } elseif ($driver === 'sqlite' && Schema::hasTable('check_outs')) {
            if (DB::table('check_outs')->where('status', 'cancelled')->count() === 0) {
                Schema::create('check_outs_new', fn (Blueprint $table) => $this->blueprint($table, false));
                DB::table('check_outs_new')->insertUsing(self::COLUMNS, DB::table('check_outs')->select(self::COLUMNS));
                Schema::drop('check_outs');
                Schema::rename('check_outs_new', 'check_outs');
            }
        }
    }
};
