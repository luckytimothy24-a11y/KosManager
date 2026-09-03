<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Kamar;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireOldBookings extends Command
{
    protected $signature = 'booking:expire-old';

    protected $description = 'Kedaluwarsakan booking approved yang sudah melewati tanggal selesai tanpa check-in';

    public function handle(): int
    {
        $expiredApproved = $this->expireUncheckedInApprovedBookings();

        $this->info("{$expiredApproved} booking approved tanpa check-in ditandai expired.");

        return self::SUCCESS;
    }

    private function expireUncheckedInApprovedBookings(): int
    {
        $expiredCount = 0;

        $bookingIds = Booking::where('status', 'approved')
            ->whereDate('end_date', '<', today())
            ->pluck('id');

        foreach ($bookingIds as $bookingId) {
            try {
                $expired = DB::transaction(function () use ($bookingId) {
                    $booking = Booking::whereKey($bookingId)->lockForUpdate()->first();

                    if (! $booking || $booking->status !== 'approved') {
                        return null;
                    }

                    if (! $booking->end_date->copy()->startOfDay()->lt(today())) {
                        return null;
                    }

                    $kamar = Kamar::whereKey($booking->kamar_id)->lockForUpdate()->first();

                    $booking->update(['status' => 'expired']);

                    if ($kamar && $kamar->status === 'booked') {
                        $stillReserved = Booking::where('kamar_id', $kamar->id)
                            ->whereKeyNot($booking->id)
                            ->where('status', 'approved')
                            ->whereDate('end_date', '>=', today())
                            ->exists();

                        if (! $stillReserved) {
                            $kamar->update(['status' => 'available']);
                        }
                    }

                    return $booking;
                });

                if ($expired) {
                    $expiredCount++;
                    NotificationService::bookingExpired($expired->user_id, $expired->booking_code, "booking-expired:{$expired->id}");
                }
            } catch (\Exception $e) {
                \Log::warning("booking:expire-old failed for booking {$bookingId}: {$e->getMessage()}");
            }
        }

        return $expiredCount;
    }
}
