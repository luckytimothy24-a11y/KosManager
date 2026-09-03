<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackup\DatabaseBackupException;
use App\Services\DatabaseBackup\DatabaseBackupManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RestoreDatabaseBackup extends Command
{
    protected $signature = 'backup:restore {file : Nama file backup di disk}
        {--disk= : Nama disk penyimpanan backup}
        {--connection= : Nama koneksi database}
        {--force : Lewati konfirmasi}';

    protected $description = 'Pulihkan database dari file backup (DESTRUKTIF — data saat ini akan diganti)';

    public function handle(): int
    {
        $disk = $this->option('disk') ?? config('backup.disk', 'backups');
        $file = $this->argument('file');

        if (! $this->option('force')) {
            $confirmed = $this->confirm('Semua data saat ini akan DIGANTI dengan isi backup. Lanjutkan?', false);
            if (! $confirmed) {
                $this->warn('Restore dibatalkan.');

                return self::SUCCESS;
            }
        }

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

            if (! $driver->verify((string) $content)) {
                $this->error("File backup [{$file}] tidak lolos verifikasi — restore dibatalkan.");

                return self::FAILURE;
            }

            $driver->restore((string) $content);
            $this->info("Restore berhasil dari [{$file}].");

            return self::SUCCESS;
        } catch (DatabaseBackupException|Throwable $e) {
            $this->error('Restore GAGAL: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
