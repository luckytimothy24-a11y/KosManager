<?php

namespace App\Services\DatabaseBackup;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;
use Throwable;

/**
 * Driver backup untuk koneksi SQLite (termasuk file & in-memory).
 *
 * Strategi: membuat dump SQL portabel (schema dari sqlite_master + data
 * sebagai INSERT). Restore menjalankan ulang dump dalam satu transaksi
 * sehingga konsisten; kegagalan memicu rollback penuh.
 */
class SqliteBackupDriver implements DatabaseBackupContract
{
    public function __construct(protected string $connection = 'sqlite') {}

    public function driverName(): string
    {
        return 'sqlite';
    }

    public function dump(): string
    {
        try {
            $pdo = DB::connection($this->connection)->getPdo();
            $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name")
                ->fetchAll(PDO::FETCH_COLUMN);

            $lines = [
                'PRAGMA foreign_keys = OFF;',
                'BEGIN;',
                '',
            ];

            foreach ($tables as $table) {
                $stmt = $pdo->prepare('SELECT sql FROM sqlite_master WHERE type = "table" AND name = ?');
                $stmt->execute([$table]);
                $createSql = (string) ($stmt->fetchColumn() ?? '');

                $lines[] = 'DROP TABLE IF EXISTS '.$this->quoteIdent($table).';';
                if ($createSql !== '') {
                    $lines[] = $createSql.';';
                }

                $columns = Schema::connection($this->connection)->getColumnListing($table);
                $rows = DB::connection($this->connection)->table($table)->get();

                foreach ($rows as $row) {
                    $values = array_map(
                        fn ($col) => $this->quoteValue($pdo, $row->{$col}),
                        $columns
                    );
                    $lines[] = 'INSERT INTO '.$this->quoteIdent($table)
                        .' ('.implode(', ', array_map(fn ($c) => $this->quoteIdent($c), $columns)).')'
                        .' VALUES ('.implode(', ', $values).');';
                }

                $lines[] = '';
            }

            $lines[] = 'COMMIT;';
            $lines[] = 'PRAGMA foreign_keys = ON;';

            return implode(PHP_EOL, $lines);
        } catch (Throwable $e) {
            throw new DatabaseBackupException('Gagal membuat dump SQLite: '.$e->getMessage(), 0, $e);
        }
    }

    public function restore(string $content): void
    {
        $pdo = DB::connection($this->connection)->getPdo();

        try {
            $pdo->exec($content);
        } catch (Throwable $e) {
            try {
                $pdo->exec('ROLLBACK;');
            } catch (Throwable) {
            }

            throw new DatabaseBackupException('Gagal restore SQLite: '.$e->getMessage(), 0, $e);
        }
    }

    public function verify(string $content): bool
    {
        try {
            $pdo = new PDO('sqlite::memory:');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec($content);

            $count = $pdo->query('SELECT COUNT(*) FROM sqlite_master WHERE type = "table" AND name NOT LIKE "sqlite_%"')
                ->fetchColumn();

            return (int) $count > 0;
        } catch (Throwable) {
            return false;
        }
    }

    public function supported(): bool
    {
        return true;
    }

    private function quoteIdent(string $identifier): string
    {
        return '"'.str_replace('"', '""', $identifier).'"';
    }

    private function quoteValue(PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return $pdo->quote((string) $value);
    }
}
