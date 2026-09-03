<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Kamar;
use Illuminate\Support\Str;

class BookingService
{
    /**
     * Buat booking dengan business rule existing:
     * - transaksi + lockForUpdate pada kamar untuk mencegah race condition
     * - kamar harus berstatus available & kos aktif
     * - deteksi overlap terhadap booking approved pada periode yang sama
     * - harga diambil dari harga existing (harian/bulanan)
     * - booking dibuat berstatus approved (instant booking)
     * - kamar di-set booked
     *
     * @return array{ok: bool, booking?: Booking, error?: string}
     */
    public function create(array $payload, int $userId): array
    {
        $result = \DB::transaction(function () use ($payload, $userId) {
            $kamar = Kamar::whereKey($payload['kamar_id'])->lockForUpdate()->firstOrFail();

            if ($kamar->status !== 'available' || $kamar->kos->status !== 'active') {
                return ['ok' => false, 'error' => 'kamar_tidak_tersedia'];
            }

            $hasConflict = Booking::where('kamar_id', $kamar->id)
                ->where('status', 'approved')
                ->where('start_date', '<', $payload['end_date'])
                ->where('end_date', '>', $payload['start_date'])
                ->exists();

            if ($hasConflict) {
                return ['ok' => false, 'error' => 'overlap'];
            }

            $price = $payload['rental_type'] === 'daily' ? $kamar->daily_price : $kamar->monthly_price;

            if (! $price) {
                return ['ok' => false, 'error' => 'no_price'];
            }

            $booking = Booking::create([
                'booking_code' => 'BK-'.strtoupper(Str::random(8)),
                'user_id' => $userId,
                'kos_id' => $kamar->kos_id,
                'kamar_id' => $kamar->id,
                'booking_date' => now(),
                'start_date' => $payload['start_date'],
                'end_date' => $payload['end_date'],
                'rental_type' => $payload['rental_type'],
                'price' => $price,
                'status' => 'approved',
                'notes' => $payload['notes'] ?? null,
            ]);

            $kamar->update(['status' => 'booked']);

            return ['ok' => true, 'booking' => $booking];
        });

        if (($result['ok'] ?? false) && isset($result['booking'])) {
            $booking = $result['booking'];
            $booking->load(['user', 'kos', 'kamar']);

            try {
                NotificationService::bookingInstantConfirmation($booking->user_id, $booking->booking_code, $booking->kamar->room_number);
                NotificationService::bookingNew($booking->kos->owner_id, $booking->user->name, $booking->kamar->room_number);
                AuditLogService::create('Booking', "Booking baru {$booking->user->name} untuk kamar {$booking->kamar->room_number} (instant booking)", ['kos_id' => $booking->kos_id, 'kamar_id' => $booking->kamar_id]);
            } catch (\Exception $e) {
                \Log::warning('Booking notification/audit failed: '.$e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Batalkan booking dengan business rule existing:
     * - transaksi + lockForUpdate pada booking (concurrency protection)
     * - hanya status cancellable (pending/approved) yang dapat dibatalkan
     * - kamar kembali available bila tidak ada booking approved lain yang masih
     *   memesan kamar tersebut dengan end_date belum lewat.
     *
     * @return bool true bila berhasil dibatalkan
     */
    public function cancel(int $bookingId): bool
    {
        return (bool) \DB::transaction(function () use ($bookingId) {
            $booking = Booking::whereKey($bookingId)->lockForUpdate()->first();

            if (! $booking || ! $booking->isCancellable()) {
                return false;
            }

            $booking->update(['status' => 'cancelled']);

            $kamar = Kamar::whereKey($booking->kamar_id)->lockForUpdate()->first();

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

            return true;
        });
    }
}
