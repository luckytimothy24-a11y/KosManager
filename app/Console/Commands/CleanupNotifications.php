<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class CleanupNotifications extends Command
{
    protected $signature = 'notification:cleanup {--days=90 : Hapus notifikasi lebih lama dari N hari}';

    protected $description = 'Hapus notifikasi yang sudah lebih lama dari periode tertentu';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $deleted = NotificationService::cleanup($days);

        $this->info("{$deleted} notifikasi dihapus (lebih lama dari {$days} hari).");

        return self::SUCCESS;
    }
}
