<?php

namespace App\Console\Commands;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class MarkOverdueTagihans extends Command
{
    protected $signature = 'tagihan:mark-overdue';

    protected $description = 'Tandai tagihan yang melewati jatuh tempo dan belum ada pembayaran sebagai overdue';

    public function handle(): int
    {
        $tagihans = Tagihan::with(['penghuni.user', 'kontrak.kos.owner', 'pembayarans'])
            ->where('status', 'unpaid')
            ->whereDate('due_date', '<', today())
            ->get();

        $marked = 0;

        foreach ($tagihans as $tagihan) {
            // Skip tagihan yang sudah memiliki pembayaran (apun status-nya)
            // Ini menjamin: pending_verification tidak ikut jadi overdue
            // Aturan bisnis: Overdue = due_date < today AND tidak ada pembayaran valid
            $hasPayment = $tagihan->pembayarans->isNotEmpty();

            if ($hasPayment) {
                // Sudah ada pembayaran (apapun statusnya) -> BUKAN overdue
                continue;
            }

            // Update atomik bersyarat: hanya transisi dari 'unpaid' -> 'overdue'.
            // Jika worker lain sudah lebih dulu menandainya, update ini memengaruhi
            // 0 baris sehingga tidak terjadi notifikasi ganda / transisi konflik
            // ketika command dijalankan berulang atau konkuren.
            // Denda: terapapikan fixed_amount dari config jika penalty masih 0.
            // Jika owner sudah set manual penalty, jangan timpa (preserve).
            $lateFee = (float) config('kosmanager.late_fee.fixed_amount', 50000);

            $updated = \DB::transaction(function () use ($tagihan, $lateFee) {
                $locked = Tagihan::whereKey($tagihan->id)->lockForUpdate()->first();

                if (! $locked || $locked->status !== 'unpaid') {
                    return null;
                }

                // Double-check: pastikan tidak ada pembayaran terkait
                // (cek ulang di dalam transaksi untuk safety)
                if ($locked->pembayarans()->exists()) {
                    return null;
                }

                if ($locked->due_date->startOfDay()->gte(now()->startOfDay())) {
                    return null;
                }

                $updateData = ['status' => 'overdue'];

                if ((float) $locked->penalty == 0) {
                    $updateData['penalty'] = $lateFee;
                    $updateData['total'] = (float) $locked->subtotal - (float) $locked->discount + $lateFee;
                }

                $locked->update($updateData);

                return $locked->fresh();
            });

            if ($updated === null) {
                continue;
            }

            $marked++;
            $tagihan = $updated;

            if ($tagihan->penghuni) {
                try {
                    NotificationService::billOverdue(
                        $tagihan->penghuni->user_id,
                        $tagihan->bill_number,
                        'Rp '.number_format($tagihan->penalty, 0, ',', '.'),
                        'Rp '.number_format($tagihan->total, 0, ',', '.'),
                        "bill-overdue:{$tagihan->id}"
                    );
                } catch (\Exception $e) {
                    \Log::warning("tagihan:mark-overdue notification failed for tagihan {$tagihan->id}: {$e->getMessage()}");
                }

                if ($tagihan->kontrak?->kos?->owner) {
                    try {
                        NotificationService::billOverdueOwner(
                            $tagihan->kontrak->kos->owner->id,
                            $tagihan->penghuni->user->name ?? 'N/A',
                            $tagihan->bill_number,
                            number_format($tagihan->total, 0, ',', '.'),
                            "bill-overdue-owner:{$tagihan->id}"
                        );
                    } catch (\Exception $e) {
                        \Log::warning("tagihan:mark-overdue owner notification failed for tagihan {$tagihan->id}: {$e->getMessage()}");
                    }
                }
            }
        }

        $this->info("{$marked} tagihan ditandai overdue.");

        return self::SUCCESS;
    }
}
