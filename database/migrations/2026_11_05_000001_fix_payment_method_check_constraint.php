<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix payment_method CHECK constraint to include 'qris' (used by application)
     * instead of 'cash' (which was incorrectly set in the SQLite database).
     *
     * The migration 2024_01_01_000012 defines:
     *   enum('payment_method', ['transfer_bank', 'qris', 'e_wallet'])
     * But the actual SQLite database has:
     *   check ("payment_method" in ('transfer_bank', 'cash', 'e_wallet'))
     *
     * This migration aligns the database constraint with the application code.
     */
    public function up(): void
    {
        $driver = DB::connection()->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $this->rebuildSqliteConstraint(
                "payment_method\" in ('transfer_bank', 'qris', 'e_wallet')",
                ['cash' => 'transfer_bank']
            );

            return;
        }

        // MySQL: map nilai yang akan dihapus sebelum enum dipersempit.
        DB::table('pembayarans')->where('payment_method', 'cash')->update([
            'payment_method' => 'transfer_bank',
        ]);

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->enum('payment_method', ['transfer_bank', 'qris', 'e_wallet'])->change();
        });
    }

    public function down(): void
    {
        $driver = DB::connection()->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $this->rebuildSqliteConstraint(
                "payment_method\" in ('transfer_bank', 'cash', 'e_wallet')",
                ['qris' => 'transfer_bank']
            );

            return;
        }

        DB::table('pembayarans')->where('payment_method', 'qris')->update([
            'payment_method' => 'transfer_bank',
        ]);

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->enum('payment_method', ['transfer_bank', 'cash', 'e_wallet'])->change();
        });
    }

    /**
     * Recreate the SQLite table with a new payment_method CHECK constraint.
     * Returns early when the constraint already matches, so a repeated run is safe.
     *
     * @param  array<string, string>  $valueMap
     */
    private function rebuildSqliteConstraint(string $constraint, array $valueMap): void
    {
        $currentSql = DB::select("SELECT sql FROM sqlite_master WHERE name='pembayarans' AND type='table'")[0]->sql;

        $newSql = preg_replace(
            "/payment_method\" in \([^)]*\)/",
            $constraint,
            $currentSql,
            1
        );

        if ($newSql === null || $newSql === $currentSql) {
            return;
        }

        foreach ($valueMap as $from => $to) {
            DB::table('pembayarans')->where('payment_method', $from)->update([
                'payment_method' => $to,
            ]);
        }

        $newSql = preg_replace('/CREATE TABLE "pembayarans"/', 'CREATE TABLE "pembayarans_new"', $newSql, 1);

        DB::statement('PRAGMA foreign_keys=OFF');

        try {
            DB::statement($newSql);

            $columns = DB::select("PRAGMA table_info('pembayarans')");
            $columnNames = array_map(fn ($column) => '"'.$column->name.'"', $columns);
            $columnsList = implode(', ', $columnNames);

            DB::statement("INSERT INTO pembayarans_new ({$columnsList}) SELECT {$columnsList} FROM pembayarans");
            DB::statement('DROP TABLE pembayarans');
            DB::statement('ALTER TABLE pembayarans_new RENAME TO pembayarans');
        } finally {
            DB::statement('PRAGMA foreign_keys=ON');
        }
    }
};
