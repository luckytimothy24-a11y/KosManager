<?php

namespace App\Console\Commands;

use App\Models\Tagihan;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class RemindOverdueEscalation extends Command
{
    protected $signature = 'tagihan:remind-overdue-escalation {--days=7 : Berapa hari overdue sebelum escalation ke owner}';

    protected $description = 'Kirim notifikasi escalation ke owner untuk tagihan yang overdue lebih dari N hari';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $threshold = now()->subDays($days)->startOfDay();

        $tagihans = Tagihan::with(['penghuni.user', 'kontrak.kos.owner'])
            ->where('status', 'overdue')
            ->whereDate('due_date', '<', $threshold)
            ->whereDate('due_date', '>=', $threshold->subDay())
            ->get();

        $notified = 0;

        foreach ($tagihans as $tagihan) {
            if (! $tagihan->penghuni || ! $tagihan->kontrak?->kos?->owner) {
                continue;
            }

            $ownerId = $tagihan->kontrak->kos->owner->id;
            $tenantName = $tagihan->penghuni->user->name ?? 'N/A';
            $daysOverdue = now()->diffInDays($tagihan->due_date);
            $uniqueKey = "bill-overdue-escalation:{$tagihan->id}";

            try {
                NotificationService::billOverdueEscalation(
                    $ownerId,
                    $tagihan->bill_number,
                    $tenantName,
                    $daysOverdue,
                    $uniqueKey,
                );
                $notified++;
            } catch (\Exception $e) {
                \Log::warning("tagihan:remind-overdue-escalation failed for tagihan {$tagihan->id}: {$e->getMessage()}");
            }
        }

        $this->info("{$notified} escalation notifications sent for bills overdue > {$days} days.");

        return self::SUCCESS;
    }
}
