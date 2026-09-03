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
            ->where('status', 'unpaid')
            ->whereDate('due_date', '<', today())
            ->get();

        $marked = 0;

        foreach ($tagihans as $tagihan) {
            // Update atomik bersyarat: hanya transisi dari 'unpaid' -> 'overdue'.
            // Jika worker lain sudah lebih dulu menandainya, update ini memengaruhi
            // 0 baris sehingga tidak terjadi notifikasi ganda / transisi konflik
            // ketika command dijalankan berulang atau konkuren.
            $changed = Tagihan::whereKey($tagihan->id)
                ->where('status', 'unpaid')
                ->whereDate('due_date', '<', today())
                ->update(['status' => 'overdue']);

            if ($changed === 0) {
                continue;
            }

            $marked++;

            if ($tagihan->penghuni) {
                try {
                    NotificationService::billOverdue($tagihan->penghuni->user_id, $tagihan->bill_number, "bill-overdue:{$tagihan->id}");
                } catch (\Exception $e) {
                    \Log::warning("tagihan:mark-overdue notification failed for tagihan {$tagihan->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info("{$marked} tagihan ditandai overdue.");

        return self::SUCCESS;
    }
}
