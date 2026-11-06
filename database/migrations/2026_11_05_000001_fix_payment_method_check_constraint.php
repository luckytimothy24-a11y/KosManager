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
            $this->fixSqlitePaymentMethod();
        } else {
            // MySQL / PostgreSQL: alter column enum
            Schema::table('pembayarans', function (Blueprint $table) {
                $table->enum('payment_method', ['transfer_bank', 'qris', 'e_wallet'])
                    ->default('transfer_bank')
                    ->change();
            });
        }
    }

    private function fixSqlitePaymentMethod(): void
    {
        // Get current table SQL
        $currentSql = DB::select("SELECT sql FROM sqlite_master WHERE name='pembayarans' AND type='table'")[0]->sql;

        // Replace the CHECK constraint for payment_method
        $newSql = preg_replace(
            "/payment_method\" in \('transfer_bank', 'cash', 'e_wallet'\)/",
            "payment_method\" in ('transfer_bank', 'qris', 'e_wallet')",
            $currentSql
        );

        if ($newSql === $currentSql) {
            // Fallback: try alternative pattern
            $newSql = preg_replace(
                "/payment_method\" in \([^)]*\)/",
                "payment_method\" in ('transfer_bank', 'qris', 'e_wallet')",
                $currentSql
            );
        }

        // Enable foreign keys for safe recreation
        DB::statement('PRAGMA foreign_keys=OFF');

        // Create new table with corrected SQL using a temp name
        $newSql = preg_replace('/CREATE TABLE "pembayarans"/', 'CREATE TABLE "pembayarans_new"', $newSql);
        DB::statement($newSql);

        // Copy data from old to new
        $columns = DB::select("PRAGMA table_info('pembayarans')");
        $columnNames = array_map(fn($c) => $c->name, $columns);
        $columnsList = implode(', ', $columnNames);

        DB::statement("INSERT INTO pembayarans_new SELECT {$columnsList} FROM pembayarans");

        // Drop old table
        DB::statement('DROP TABLE pembayarans');

        // Rename new table
        DB::statement('ALTER TABLE pembayarans_new RENAME TO pembayarans');

        // Re-enable foreign keys
        DB::statement('PRAGMA foreign_keys=ON');
    }

    public function down(): void
    {
        $driver = DB::connection()->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            // Revert to original CHECK constraint
            $currentSql = DB::select("SELECT sql FROM sqlite_master WHERE name='pembayarans' AND type='table'")[0]->sql;

            $newSql = preg_replace(
                "/payment_method\" in \('transfer_bank', 'qris', 'e_wallet'\)/",
                "payment_method\" in ('transfer_bank', 'cash', 'e_wallet')",
                $currentSql
            );

            DB::statement('PRAGMA foreign_keys=OFF');

            $newSql = preg_replace('/CREATE TABLE "pembayarans"/', 'CREATE TABLE "pembayarans_new"', $newSql);
            DB::statement($newSql);

            $columns = DB::select("PRAGMA table_info('pembayarans')");
            $columnNames = array_map(fn($c) => $c->name, $columns);
            $columnsList = implode(', ', $columnNames);

            DB::statement("INSERT INTO pembayarans_new SELECT {$columnsList} FROM pembayarans");
            DB::statement('DROP TABLE pembayarans');
            DB::statement('ALTER TABLE pembayarans_new RENAME TO pembayarans');
            DB::statement('PRAGMA foreign_keys=ON');
        } else {
            Schema::table('pembayarans', function (Blueprint $table) {
                $table->enum('payment_method', ['transfer_bank', 'cash', 'e_wallet'])
                    ->default('transfer_bank')
                    ->change();
            });
        }
    }
};