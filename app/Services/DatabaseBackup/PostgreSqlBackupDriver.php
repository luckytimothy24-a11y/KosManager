<?php

namespace App\Services\DatabaseBackup;

use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Driver backup untuk koneksi PostgreSQL via pg_dump.
 *
 * Membutuhkan biner pg_dump/psql tersedia di server. Restore menyalurkan
 * hasil dump ke psql; kegagalan proses diangkat menjadi DatabaseBackupException.
 */
class PostgreSqlBackupDriver implements DatabaseBackupContract
{
    public function __construct(protected string $connection = 'pgsql') {}

    public function driverName(): string
    {
        return 'pgsql';
    }

    public function dump(): string
    {
        $config = DB::connection($this->connection)->getConfig();

        $command = array_filter([
            config('backup.pgsql.dump_binary', 'pg_dump'),
            $this->hostArgs($config),
            '--username='.$config['username'],
            '--no-owner',
            '--no-acl',
            '--dbname='.$config['database'],
        ], fn ($v) => $v !== null && $v !== '');

        $env = ['PGPASSWORD' => (string) ($config['password'] ?? '')];
        $process = new Process($command, null, $env);
        $process->setTimeout(300);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new DatabaseBackupException('pg_dump gagal: '.$this->lastLines($process->getErrorOutput()), 0, $e);
        }

        return $process->getOutput();
    }

    public function restore(string $content): void
    {
        $config = DB::connection($this->connection)->getConfig();

        $command = array_filter([
            config('backup.pgsql.client_binary', 'psql'),
            $this->hostArgs($config),
            '--username='.$config['username'],
            '--set=ON_ERROR_STOP=1',
            '--dbname='.$config['database'],
        ], fn ($v) => $v !== null && $v !== '');

        $env = ['PGPASSWORD' => (string) ($config['password'] ?? '')];
        $process = new Process($command, null, $env);
        $process->setTimeout(600);
        $process->setInput($content);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new DatabaseBackupException('Restore PostgreSQL gagal: '.$this->lastLines($process->getErrorOutput()), 0, $e);
        }
    }

    public function verify(string $content): bool
    {
        return str_contains($content, 'CREATE TABLE') || str_contains($content, 'COPY ');
    }

    public function supported(): bool
    {
        return extension_loaded('pdo_pgsql');
    }

    private function hostArgs(array $config): ?string
    {
        return isset($config['host']) && $config['host'] !== '' ? '--host='.$config['host'] : null;
    }

    private function lastLines(string $output): string
    {
        $lines = array_filter(array_map('trim', explode(PHP_EOL, $output)));

        return implode(' | ', array_slice($lines, -3));
    }
}
