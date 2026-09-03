<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ListDatabaseBackups extends Command
{
    protected $signature = 'backup:list {--disk= : Nama disk penyimpanan backup}';

    protected $description = 'Tampilkan daftar file backup yang tersedia';

    public function handle(): int
    {
        $disk = $this->option('disk') ?? config('backup.disk', 'backups');
        $prefix = config('backup.filename_prefix', 'backup-');

        $storage = Storage::disk($disk);
        $files = collect($storage->allFiles())
            ->filter(fn ($file) => str_starts_with(basename($file), $prefix) && str_ends_with($file, '.sql'))
            ->sortByDesc(fn ($file) => $storage->lastModified($file))
            ->values();

        if ($files->isEmpty()) {
            $this->warn("Belum ada backup di disk [{$disk}].");

            return self::SUCCESS;
        }

        $rows = $files->map(function ($file) use ($storage) {
            $modified = Carbon::createFromTimestamp($storage->lastModified($file));

            return [
                basename($file),
                number_format($storage->size($file), 0, ',', '.'),
                $modified->format('Y-m-d H:i:s'),
            ];
        })->all();

        $this->table(['File', 'Ukuran (bytes)', 'Terakhir diubah'], $rows);

        return self::SUCCESS;
    }
}
