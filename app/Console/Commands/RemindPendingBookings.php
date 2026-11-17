<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class RemindPendingBookings extends Command
{
    protected $signature = 'booking:remind-pending {--days=1 : Berapa hari sebelum booking kedaluwarsa pengingat dikirim}';

    protected $description = 'Kirim pengingat untuk booking pending yang menunggu pembayaran dan mendekati kedaluwarsa';

    public function handle(): int
    {
        // Booking pending tanpa pembayaran yang dibuat lebih dari N hari lalu.
        // Booking pending tanpa pembayaran approved dalam X hari akan expired.
        $days = (int) $this->option('days');
        $threshold = now()->subDays($days);

        $bookings = Booking::where('status', 'pending')
            ->where('created_at', '<=', $threshold)
            ->whereDoesntHave('pembayarans', function ($q) {
                $q->where('verification_status', 'approved');
            })
            ->with(['user', 'kos'])
            ->get();

        $count = 0;
        foreach ($bookings as $booking) {
            if (! $booking->user) {
                continue;
            }

            try {
                NotificationService::bookingPendingReminder(
                    $booking->user_id,
                    $booking->booking_code,
                    "booking-pending-reminder:{$booking->id}:" . today()->toDateString()
                );
                $count++;
            } catch (\Exception $e) {
                \Log::warning("booking:remind-pending notification failed for booking {$booking->id}: {$e->getMessage()}");
            }
        }

        $this->info("{$count} pengingat booking pending dikirim.");
        return self::SUCCESS;
    }
}