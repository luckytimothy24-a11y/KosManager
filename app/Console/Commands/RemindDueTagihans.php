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

        $tagihans = Tagihan::with(['penghuni.user', 'kontrak.kos.owner'])
            ->where('status', 'unpaid')
            ->whereDate('due_date', '=', $targetDate)
            ->get();

        foreach ($tagihans as $tagihan) {
            if (! $tagihan->penghuni) {
                continue;
            }

            $tenantUser = $tagihan->penghuni->user;
            $tenantEmail = $tenantUser?->email;

            try {
                NotificationService::billDueSoon(
                    $tagihan->penghuni->user_id,
                    $tagihan->bill_number,
                    $tagihan->due_date->format('d/m/Y'),
                    "bill-due:{$tagihan->id}"
                );

                // WhatsApp reminder
                $tenantPhone = $tenantUser?->phone ?? $tagihan->penghuni->phone ?? null;
                if ($tenantPhone) {
                    \App\Services\WhatsAppNotificationService::send(
                        $tenantPhone,
                        "Pengingat: Tagihan {$tagihan->bill_number} jatuh tempo {$tagihan->due_date->format('d/m/Y')}. Total: Rp " . number_format($tagihan->total, 0, ',', '.') . ". Segera bayar di KosManager."
                    );
                }
            } catch (\Exception $e) {
                \Log::warning("tagihan:remind-due-soon notification failed for tagihan {$tagihan->id}: {$e->getMessage()}");
            }

            if ($tagihan->kontrak?->kos?->owner) {
                $ownerEmail = $tagihan->kontrak->kos->owner->email;
                try {
                    NotificationService::billDueSoonOwner(
                        $tagihan->kontrak->kos->owner->id,
                        $tagihan->penghuni->user->name ?? 'N/A',
                        $tagihan->bill_number,
                        $tagihan->due_date->format('d/m/Y'),
                        "bill-due-owner:{$tagihan->id}"
                    );

                    // Email reminder to owner
                    if ($ownerEmail) {
                        \Illuminate\Support\Facades\Mail::to($ownerEmail)->send(
                            new \App\Mail\KosManagerMail(
                                "Pengingat: Tagihan {$tagihan->bill_number} Tenant Mendekati Jatuh Tempo",
                                "Tagihan {$tagihan->bill_number} milik {$tagihan->penghuni->user->name} akan jatuh tempo pada {$tagihan->due_date->format('d/m/Y')}. Total: Rp " . number_format($tagihan->total, 0, ',', '.') . ".",
                                route('owner.tagihan.show', $tagihan),
                                'Lihat Tagihan'
                            )
                        );
                    }
                } catch (\Exception $e) {
                    \Log::warning("tagihan:remind-due-soon owner notification failed for tagihan {$tagihan->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info("{$tagihans->count()} pengingat tagihan dikirim.");

        return self::SUCCESS;
    }
}
