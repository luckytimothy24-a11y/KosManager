<?php

namespace App\Console\Commands;

use App\Services\AdvertisingService;
use Illuminate\Console\Command;

class AdvertisingProcessCampaigns extends Command
{
    protected $signature = 'advertising:process-campaigns';

    protected $description = 'Mengaktifkan kampanye advertising yang sudah waktunya tayang dan menandai yang sudah selesai';

    public function handle(AdvertisingService $advertising): int
    {
        $activated = $advertising->activateEligible();
        $completed = $advertising->completeExpired();

        $this->info("{$activated} kampanye diaktifkan, {$completed} kampanye ditandai selesai.");

        return self::SUCCESS;
    }
}
