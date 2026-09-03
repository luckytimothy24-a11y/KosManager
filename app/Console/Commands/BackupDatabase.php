<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackup\DatabaseBackupContract;
use App\Services\DatabaseBackup\DatabaseBackupException;
use App\Services\DatabaseBackup\DatabaseBackupManager;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class BackupDatabase extends Command
{
    protected $signature = 'backup:run
        {--disk= : Nama disk penyimpanan backup}
        {--keep= : Override jumlah hari retention}
        {--connection= : Nama koneksi database}';

    protected $description = 'Buat backup database dan bersihkan backup lama sesuai retention';

    public function handle(): int
    {
        $disk = $this->option('disk') ?? config('backup.disk', 'backups');

        try {
            $driver = DatabaseBackupManager::driver($this->option('connection'));

            if (! $driver->supported()) {
                throw new DatabaseBackupException('Driver '.$driver->driverName().' tidak tersedia di server ini.');
            }

            $content = $driver->dump();
            if (trim($content) === '') {
                throw new DatabaseBackupException('Backup kosong (tidak ada data yang dihasilkan).');
            }

            return $this->store($disk, $driver, $content);
        } catch (DatabaseBackupException $e) {
            $this->error('Backup GAGAL: '.$e->getMessage());

            return self::FAILURE;
        } catch (Throwable $e) {
            $this->error('Backup GAGAL (tidak terduga): '.$e->getMessage());

            return self::FAILURE;
        }
    }

    private function store(string $disk, DatabaseBackupContract $driver, string $content): int
    {
        $storage = Storage::disk($disk);
        $prefix = config('backup.filename_prefix', 'backup-');
        $filename = $prefix.Carbon::now()->format('Y-m-d_His').'-'.Str::lower(Str::random(4)).'.sql';

        if (! $storage->put($filename, $content) || ! $storage->exists($filename)) {
            throw new DatabaseBackupException("Tidak dapat menulis file backup [{$filename}] ke disk [{$disk}].");
        }

        $size = number_format($storage->size($filename), 0, ',', '.');

        $this->info("Backup berhasil: [{$disk}]/{$filename} ({$size} bytes)");

        $this->prune($disk, $driver->driverName());

        return self::SUCCESS;
    }

    /**
     * Bersihkan backup lama sesuai retention.
     */
    private function prune(string $disk, string $driverName): void
    {
        $keepDays = (int) ($this->option('keep') ?? config('backup.retention_days', 30));
        $prefix = config('backup.filename_prefix', 'backup-');

        $storage = Storage::disk($disk);
        $cutoff = Carbon::now()->subDays($keepDays);

        $removed = 0;

        foreach ($storage->allFiles() as $file) {
            if (! str_starts_with(basename($file), $prefix) || ! str_ends_with($file, '.sql')) {
                continue;
            }

            $modified = Carbon::createFromTimestamp($storage->lastModified($file));

            if ($modified->lessThan($cutoff)) {
                $storage->delete($file);
                $removed++;
            }
        }

        if ($removed > 0) {
            $this->info("Backup retention: {$removed} file lama dihapus (retention {$keepDays} hari).");
        } else {
            $this->info("Backup retention: tidak ada file yang perlu dibersihkan (retention {$keepDays} hari).");
        }

        $this->components->twoColumnDetail('Driver', $driverName);
    }
}
