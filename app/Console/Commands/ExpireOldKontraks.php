<?php

namespace App\Console\Commands;

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

        $kontrakIds = Kontrak::where('status', 'active')
            ->whereDate('end_date', '<', today())
            ->pluck('id');

        foreach ($kontrakIds as $kontrakId) {
            $autoCheckedOut = DB::transaction(function () use ($kontrakId) {
                $kontrak = Kontrak::whereKey($kontrakId)->lockForUpdate()->first();

                if (! $kontrak || $kontrak->status !== 'active') {
                    return false;
                }

                $kontrak->update(['status' => 'expired']);

                return $this->autoCheckoutPenghuni($kontrak);
            });

            $expiredCount++;

            if ($autoCheckedOut) {
                $autoCheckoutCount++;
            }
        }

        // Sweep lanjutan: kontrak yang sudah expired pada run sebelumnya tetapi
        // penghuninya belum dapat di-check-out karena masih memiliki tunggakan.
        $retryIds = Kontrak::where('status', 'expired')
            ->whereDate('end_date', '<', today())
            ->whereHas('penghuni', fn ($q) => $q->where('status', 'active'))
            ->pluck('id');

        foreach ($retryIds as $kontrakId) {
            $autoCheckedOut = DB::transaction(function () use ($kontrakId) {
                $kontrak = Kontrak::whereKey($kontrakId)->lockForUpdate()->first();

                if (! $kontrak || $kontrak->status !== 'expired') {
                    return false;
                }

                return $this->autoCheckoutPenghuni($kontrak);
            });

            if ($autoCheckedOut) {
                $autoCheckoutCount++;
            }
        }

        $this->info("{$expiredCount} kontrak ditandai expired. {$autoCheckoutCount} penghuni di-check-out otomatis.");

        return self::SUCCESS;
    }

    private function autoCheckoutPenghuni(Kontrak $kontrak): bool
    {
        $penghuni = $kontrak->penghuni;

        if (! $penghuni || $penghuni->status !== 'active') {
            return false;
        }

        $hasBlockingBills = Tagihan::where('penghuni_id', $penghuni->id)
            ->whereIn('status', ['unpaid', 'overdue', 'pending_verification'])
            ->exists();

        if ($hasBlockingBills) {
            // Penghuni masih menunggak: biarkan tetap menghuni. Setelah semua
            // tagihan lunas, run berikutnya akan menyelesaikan check-out otomatis.
            return false;
        }

        $kamar = Kamar::whereKey($penghuni->kamar_id)->lockForUpdate()->first();

        $penghuni->update(['status' => 'inactive']);

        if ($kamar && $kamar->status === 'occupied') {
            $kamar->update(['status' => 'available']);
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

        NotificationService::checkoutApproved($penghuni->user_id, $kamar?->room_number ?? '-');

        return true;
    }
}
