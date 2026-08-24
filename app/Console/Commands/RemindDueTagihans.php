<?php

namespace App\Console\Commands;

use App\Models\Tagihan;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class RemindDueTagihans extends Command
{
    protected $signature = 'tagihan:remind-due-soon {--days=3 : Berapa hari sebelum jatuh tempo pengingat dikirim}';

    protected $description = 'Kirim pengingat untuk tagihan unpaid yang mendekati jatuh tempo';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $targetDate = today()->addDays($days);

        $tagihans = Tagihan::with('penghuni')
            ->where('status', 'unpaid')
            ->whereDate('due_date', '=', $targetDate)
            ->get();

        foreach ($tagihans as $tagihan) {
            if (! $tagihan->penghuni) {
                continue;
            }

            NotificationService::billDueSoon(
                $tagihan->penghuni->user_id,
                $tagihan->bill_number,
                $tagihan->due_date->format('d/m/Y')
            );
        }

        $this->info("{$tagihans->count()} pengingat tagihan dikirim.");

        return self::SUCCESS;
    }
}
