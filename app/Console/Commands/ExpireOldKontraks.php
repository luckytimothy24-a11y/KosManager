<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kontrak;
use App\Models\Tagihan;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireOldKontraks extends Command
{
    protected $signature = 'kontrak:expire-old';

    protected $description = 'Ubah kontrak active yang melewati tanggal selesai menjadi expired, lalu check-out otomatis penghuni yang tidak memiliki tunggakan';

    public function handle(): int
    {
        $expiredCount = 0;
        $autoCheckoutCount = 0;
        $notifications = [];

        $kontrakIds = Kontrak::where('status', 'active')
            ->whereDate('end_date', '<', today())
            ->pluck('id');

        foreach ($kontrakIds as $kontrakId) {
            try {
                $result = DB::transaction(function () use ($kontrakId) {
                    $kontrak = Kontrak::whereKey($kontrakId)->lockForUpdate()->first();

                    if (! $kontrak || $kontrak->status !== 'active') {
                        return false;
                    }

                    $kontrak->update(['status' => 'expired']);

                    return $this->autoCheckoutPenghuni($kontrak);
                });

                if ($result !== false) {
                    $expiredCount++;
                }

                if ($result) {
                    $autoCheckoutCount++;
                    $notifications[] = $result;
                }
            } catch (\Exception $e) {
                \Log::warning("kontrak:expire-old failed for kontrak {$kontrakId}: {$e->getMessage()}");
            }
        }

        $retryIds = Kontrak::where('status', 'expired')
            ->whereDate('end_date', '<', today())
            ->whereHas('penghuni', fn ($q) => $q->where('status', 'active'))
            ->pluck('id');

        foreach ($retryIds as $kontrakId) {
            try {
                $result = DB::transaction(function () use ($kontrakId) {
                    $kontrak = Kontrak::whereKey($kontrakId)->lockForUpdate()->first();

                    if (! $kontrak || $kontrak->status !== 'expired') {
                        return false;
                    }

                    return $this->autoCheckoutPenghuni($kontrak);
                });

                if ($result) {
                    $autoCheckoutCount++;
                    $notifications[] = $result;
                }
            } catch (\Exception $e) {
                \Log::warning("kontrak:expire-old retry failed for kontrak {$kontrakId}: {$e->getMessage()}");
            }
        }

        foreach ($notifications as $notif) {
            try {
                NotificationService::checkoutApproved($notif['user_id'], $notif['room_number'], "checkout-auto:{$notif['penghuni_id']}");
            } catch (\Exception $e) {
                \Log::warning("kontrak:expire-old notification failed for penghuni {$notif['penghuni_id']}: {$e->getMessage()}");
            }
        }

        $this->info("{$expiredCount} kontrak ditandai expired. {$autoCheckoutCount} penghuni di-check-out otomatis.");

        return self::SUCCESS;
    }

    private function autoCheckoutPenghuni(Kontrak $kontrak): array|false
    {
        $penghuni = $kontrak->penghuni;

        if (! $penghuni || $penghuni->status !== 'active') {
            return false;
        }

        $hasBlockingBills = Tagihan::where('penghuni_id', $penghuni->id)
            ->outstanding()
            ->exists();

        if ($hasBlockingBills) {
            return false;
        }

        $hasExistingCheckout = CheckOut::where('penghuni_id', $penghuni->id)
            ->active()
            ->exists();

        if ($hasExistingCheckout) {
            return false;
        }

        $kamar = Kamar::whereKey($penghuni->kamar_id)->lockForUpdate()->first();

        $penghuni->update(['status' => 'inactive']);

        if ($kamar && $kamar->status === 'occupied') {
            // Konsisten dengan flow check-out: bila masih ada booking approved
            // yang mencakup periode masa depan pada kamar ini, kamar kembali ke
            // status 'booked', bukan 'available'.
            $stillReserved = Booking::where('kamar_id', $kamar->id)
                ->where('status', 'approved')
                ->whereDate('end_date', '>=', today())
                ->exists();

            $kamar->update(['status' => $stillReserved ? 'booked' : 'available']);
        }

        CheckOut::create([
            'penghuni_id' => $penghuni->id,
            'kamar_id' => $penghuni->kamar_id,
            'request_date' => now(),
            'check_out_date' => now(),
            'room_condition' => null,
            'notes' => 'Check-out otomatis oleh sistem karena kontrak telah berakhir.',
            'status' => 'approved',
        ]);

        return ['user_id' => $penghuni->user_id, 'penghuni_id' => $penghuni->id, 'room_number' => $kamar?->room_number ?? '-'];
    }
}
