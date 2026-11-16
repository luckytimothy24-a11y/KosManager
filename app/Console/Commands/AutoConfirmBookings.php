<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\CheckIn;
use App\Models\Kontrak;
use App\Models\Penghuni;
use App\Models\WalletTransaction;
use App\Services\EscrowService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoConfirmBookings extends Command
{
    protected $signature = 'booking:auto-confirm';

    protected $description = 'Otomatis konfirmasi booking yang sudah memiliki bukti check-in nyata dan escrow pending';

    public function handle(EscrowService $escrowService): int
    {
        $autoConfirmHours = (int) (DB::table('settings')->where('key', 'auto_confirm_hours')->value('value') ?? 24);

        $bookings = Booking::where('status', Booking::STATUS_APPROVED)
            ->whereNotNull('start_date')
            ->where('start_date', '<=', now()->subHours($autoConfirmHours))
            ->get();

        $count = 0;

        foreach ($bookings as $booking) {
            try {
                $completed = false;

                DB::transaction(function () use ($booking, $escrowService, &$completed) {
                    $locked = Booking::whereKey($booking->id)->lockForUpdate()->first();

                    if (! $locked || $locked->status !== Booking::STATUS_APPROVED) {
                        return;
                    }

                    // H-01: Booking hanya boleh completed jika payment approved
                    // sesuai nominal server.
                    if (! $locked->hasApprovedPaymentInExpectedAmount()) {
                        return;
                    }

                    if (! $this->hasVerifiedCheckInEvidence($locked)) {
                        return;
                    }

                    $pendingEscrowCount = WalletTransaction::where('booking_id', $locked->id)
                        ->where('type', 'escrow_in')
                        ->where('status', 'pending')
                        ->count();

                    if ($pendingEscrowCount !== 1) {
                        return;
                    }

                    $locked->update(['status' => Booking::STATUS_COMPLETED]);

                    $result = $escrowService->settleEscrow($locked);

                    if (! ($result['ok'] ?? false)) {
                        throw new \RuntimeException('Settlement gagal: ' . ($result['error'] ?? 'unknown'));
                    }

                    $completed = true;
                });

                if ($completed) {
                    $count++;
                }
            } catch (\Throwable $e) {
                Log::warning('Auto-confirm booking failed', [
                    'booking_id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Auto-confirmed {$count} booking(s).");

        return Command::SUCCESS;
    }

    private function hasVerifiedCheckInEvidence(Booking $booking): bool
    {
        $penghunis = Penghuni::where('user_id', $booking->user_id)
            ->where('kos_id', $booking->kos_id)
            ->where('kamar_id', $booking->kamar_id)
            ->where('status', 'active')
            ->with(['kontraks', 'checkIns'])
            ->get();

        if ($penghunis->count() !== 1) {
            return false;
        }

        $penghuni = $penghunis->first();

        $kontrak = $penghuni->kontraks
            ->where('status', 'active')
            ->where('kos_id', $booking->kos_id)
            ->where('kamar_id', $booking->kamar_id)
            ->first(function (Kontrak $candidate) use ($booking) {
                return $candidate->start_date?->toDateString() === $booking->start_date?->toDateString()
                    && $candidate->end_date?->toDateString() === $booking->end_date?->toDateString();
            });

        if (! $kontrak) {
            return false;
        }

        return $penghuni->checkIns
            ->where('kamar_id', $booking->kamar_id)
            ->contains(function (CheckIn $checkIn) use ($booking) {
                return $checkIn->check_in_date !== null
                    && $checkIn->check_in_date->greaterThanOrEqualTo($booking->start_date);
            });
    }
}