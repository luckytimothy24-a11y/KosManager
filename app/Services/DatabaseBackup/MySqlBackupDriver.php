<?php

namespace App\Services\DatabaseBackup;

use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Driver backup untuk koneksi MySQL via mysqldump.
 *
 * Membutuhkan biner mysqldump/mysql tersedia di server. Konsistensi dump
 * memakai flag --single-transaction. Restore menyalurkan hasil dump ke
 * mysql client; kegagalan proses diangkat menjadi DatabaseBackupException.
 *
 * Kredensial password TIDAK dikirim lewat argument proses (argv) untuk
 * menghindari kebocoran pada process list. Password disalurkan lewat env
 * MYSQL_PWD (pendekatan yang sama dengan PGPASSWORD pada driver PostgreSQL).
 */
class MySqlBackupDriver implements DatabaseBackupContract
{
    public function __construct(protected string $connection = 'mysql') {}

    public function driverName(): string
    {
        return 'mysql';
    }

    public function dump(): string
    {
        $config = DB::connection($this->connection)->getConfig();
        $flags = config('backup.mysql.dump_flags', '--single-transaction --routines --triggers');

        $command = $this->arguments($config, config('backup.mysql.dump_binary', 'mysqldump'), $flags);

        $process = $this->makeProcess($command, 300);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new DatabaseBackupException('mysqldump gagal: '.$this->lastLines($process->getErrorOutput()), 0, $e);
        }

        return $process->getOutput();
    }

    public function restore(string $content): void
    {
        $config = DB::connection($this->connection)->getConfig();

        $command = $this->arguments($config, config('backup.mysql.client_binary', 'mysql'), '');

        $process = $this->makeProcess($command, 600, $content);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new DatabaseBackupException('Restore MySQL gagal: '.$this->lastLines($process->getErrorOutput()), 0, $e);
        }
    }

    public function verify(string $content): bool
    {
        return str_contains($content, 'CREATE TABLE') || str_contains($content, '-- MySQL dump');
    }

    public function supported(): bool
    {
        return extension_loaded('pdo_mysql');
    }

    /**
     * Susun argument command tanpa menyertakan password (password via env MYSQL_PWD).
     *
     * @param  string  $flags  flag tambahan (dipisah spasi); kosong untuk restore.
     * @return array<int, string>
     */
    protected function arguments(array $config, string $binary, string $flags): array
    {
        $tableFlags = $flags === ''
            ? []
            : preg_split('/\s+/', trim($flags));

        return array_values(array_filter([
            $binary,
            $this->hostArg($config),
            '--user='.$config['username'],
            ...($tableFlags === false ? [] : $tableFlags),
            $config['database'],
        ], fn ($v) => $v !== null && $v !== ''));
    }

    /**
     * Env untuk proses MySQL client: env saat ini + MYSQL_PWD (password).
     * MYSQL_PWD menggantikan flag --password agar tidak tampil di process list.
     *
     * @return array<string, string>
     */
    protected function environment(array $config): array
    {
        return array_merge($this->currentEnvironment(), [
            'MYSQL_PWD' => (string) ($config['password'] ?? ''),
        ]);
    }

    protected function currentEnvironment(): array
    {
        return array_merge($_ENV, getenv());
    }

    protected function makeProcess(array $command, int $timeout, ?string $input = null): Process
    {
        $config = DB::connection($this->connection)->getConfig();

        $process = new Process($command, null, $this->environment($config));
        $process->setTimeout($timeout);

        if ($input !== null) {
            $process->setInput($input);
        }

        return $process;
    }

    private function hostArg(array $config): ?string
    {
        return isset($config['host']) && $config['host'] !== '' ? '--host='.$config['host'] : null;
    }

    private function lastLines(string $output): string
    {
        $lines = array_filter(array_map('trim', explode(PHP_EOL, $output)));

        return implode(' | ', array_slice($lines, -3));
    }
}
