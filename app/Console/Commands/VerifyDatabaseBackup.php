<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackup\DatabaseBackupException;
use App\Services\DatabaseBackup\DatabaseBackupManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class VerifyDatabaseBackup extends Command
{
    protected $signature = 'backup:verify {file : Nama file backup di disk}
        {--disk= : Nama disk penyimpanan backup}
        {--connection= : Nama koneksi database (untuk driver) }';

    protected $description = 'Verifikasi integritas file backup database';

    public function handle(): int
    {
        $disk = $this->option('disk') ?? config('backup.disk', 'backups');
        $file = $this->argument('file');

        try {
            $storage = Storage::disk($disk);

            if (! $storage->exists($file)) {
                $this->error("File backup [{$file}] tidak ditemukan di disk [{$disk}].");

                return self::FAILURE;
            }

            $content = $storage->get($file);
            if (trim((string) $content) === '') {
                $this->error('File backup kosong.');

                return self::FAILURE;
            }

            $driver = DatabaseBackupManager::driver($this->option('connection'));

            if ($driver->verify((string) $content)) {
                $this->info("Backup [{$file}] valid.");

                return self::SUCCESS;
            }

            $this->error("Backup [{$file}] INVALID — tidak dapat dibaca sebagai dump database.");

            return self::FAILURE;
        } catch (DatabaseBackupException|Throwable $e) {
            $this->error('Verifikasi GAGAL: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
