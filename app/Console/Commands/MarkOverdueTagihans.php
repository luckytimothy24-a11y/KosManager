<?php

namespace App\Console\Commands;

use App\Models\Tagihan;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class MarkOverdueTagihans extends Command
{
    protected $signature = 'tagihan:mark-overdue';

    protected $description = 'Tandai tagihan unpaid yang melewati jatuh tempo sebagai overdue dan beri notifikasi';

    public function handle(): int
    {
        $tagihans = Tagihan::with('penghuni')
            ->whereIn('status', ['unpaid', 'pending_verification'])
            ->whereNotIn('status', ['pending_verification'])
            ->whereDate('due_date', '<', today())
            ->get();

        foreach ($tagihans as $tagihan) {
            $tagihan->update(['status' => 'overdue']);

            if ($tagihan->penghuni) {
                NotificationService::billOverdue($tagihan->penghuni->user_id, $tagihan->bill_number);
            }
        }

        $this->info("{$tagihans->count()} tagihan ditandai overdue.");

        return self::SUCCESS;
    }
}
