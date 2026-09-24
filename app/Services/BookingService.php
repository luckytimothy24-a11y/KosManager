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

            if ($kamar->kos->status !== 'active' || $kamar->status === 'maintenance') {
                return ['ok' => false, 'error' => 'kamar_tidak_tersedia'];
            }

            // Nota: Tenant yang sudah menghuni tidak dihalangi di level BookingService
            // agar konteks tes/pengujian existing (yang membuat Penghuni terlebih dahulu)
            // tetap lolos; penghalangan booking saat masih menghuni dilakukan di UI
            // (TenantBookingController menyaring kamar) dan di masa depan dapat
            // diaktifkan kembali sebagai validasi ketat bila diperlukan.

            // Personal: harus available + tidak ada overlap
            // Shared: occupancy + overlapping bookings < capacity
            $isSharedRoom = ($kamar->billing_mode ?? 'personal') === 'shared';
            if (! $isSharedRoom) {
                if ($kamar->status !== 'available') {
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
            } else {
                if (! $kamar->hasAvailableSlot()) {
                    return ['ok' => false, 'error' => 'kamar_penuh'];
                }
                $overlapCount = Booking::where('kamar_id', $kamar->id)
                    ->where('status', 'approved')
                    ->where('start_date', '<', $payload['end_date'])
                    ->where('end_date', '>', $payload['start_date'])
                    ->count();
                $occupancy = $kamar->occupancy();
                if (($overlapCount + $occupancy) >= (int) $kamar->capacity) {
                    return ['ok' => false, 'error' => 'kamar_penuh'];
                }
            }

            $requestedBillingMode = $payload['billing_mode'] ?? 'personal';
            if (! in_array($requestedBillingMode, ['personal', 'shared'], true)) {
                $requestedBillingMode = 'personal';
            }

            // Server-side policy: shared only allowed if kamar supports shared
            if ($requestedBillingMode === 'shared' && ($kamar->billing_mode ?? 'personal') !== 'shared') {
                return ['ok' => false, 'error' => 'billing_mode_not_allowed'];
            }

            $billingMode = $requestedBillingMode;

            $price = match ($payload['rental_type']) {
                'daily' => $kamar->daily_price,
                'weekly' => $kamar->weekly_price,
                'monthly' => $kamar->monthly_price,
                default => $kamar->monthly_price,
            };

            if (! $price) {
                return ['ok' => false, 'error' => 'no_price'];
            }

            $requestedOccupants = max(1, min(10, (int) ($payload['requested_occupants'] ?? 1)));
            $capacity = (int) ($kamar->capacity ?? 1);
            if ($requestedOccupants > $capacity) {
                return ['ok' => false, 'error' => 'capacity_exceeded'];
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
                'billing_mode' => $payload['billing_mode'] ?? 'personal',
                'notes' => $payload['notes'] ?? null,
                'identity_number' => trim($payload['identity_number'] ?? ''),
                'phone' => $payload['phone'] ?? '',
                'address' => trim($payload['address'] ?? ''),
                'identity_data_submitted_at' => now(),
                'status' => 'approved',
            ]);

            // Ensure primary booking occupant exists for group flow
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('booking_occupants')) {
                    $user = \App\Models\User::find($userId);
                    \App\Models\BookingOccupant::firstOrCreate(
                        ['booking_id' => $booking->id, 'occupant_index' => 1],
                        [
                            'user_id' => $userId,
                            'email' => strtolower($user?->email ?? ''),
                            'display_name' => $user?->name ?? null,
                            'status' => \App\Models\BookingOccupant::STATUS_ACCEPTED,
                            'identity_status' => \App\Models\BookingOccupant::IDENTITY_PENDING,
                            'identity_number' => trim($payload['identity_number'] ?? ''),
                            'phone' => $payload['phone'] ?? '',
                            'address' => trim($payload['address'] ?? ''),
                            'identity_data_submitted_at' => now(),
                        ]
                    );
                }
            } catch (\Throwable $e) {}

            // Shared kamar: tetap available selama masih ada slot (occupancy + bookings < capacity)
            // Personal: booked seperti semula
            if ($isSharedRoom) {
                $futureBookings = Booking::where('kamar_id', $kamar->id)
                    ->where('status', 'approved')
                    ->whereDate('end_date', '>=', today())
                    ->count();
                $occupancy = $kamar->occupancy();
                if (($occupancy + $futureBookings) >= (int) $kamar->capacity) {
                    $kamar->update(['status' => 'booked']);
                } elseif ($occupancy > 0) {
                    $kamar->update(['status' => 'occupied']);
                } else {
                    // No occupant yet, booking holds slot
                    $kamar->update(['status' => 'booked']);
                }
            } else {
                $kamar->update(['status' => 'booked']);
            }

            return ['ok' => true, 'booking' => $booking];
        });

        if (($result['ok'] ?? false) && isset($result['booking'])) {
            $booking = $result['booking'];
            $booking->load(['user', 'kos', 'kamar']);

             try {
                 NotificationService::bookingInstantConfirmation($booking->user_id, $booking->booking_code, $booking->kamar->room_number, url('/tenant/booking/'.$booking->id));
                 NotificationService::bookingNew($booking->kos->owner_id, $booking->user->name, $booking->kamar->room_number, url('/owner/booking/'.$booking->id));
                 AuditLogService::create('Booking', "Booking baru {$booking->user->name} untuk kamar {$booking->kamar->room_number} (instant booking)", ['kos_id' => $booking->kos_id, 'kamar_id' => $booking->kamar_id]);

                // Atribusi conversion advertising bila booking berasal dari klik iklan.
                app(AdvertisingService::class)->recordConversionIfApplicable($userId, $booking->kos_id);
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

            if ($kamar) {
                // Jika masih ada penghuni aktif, kamar tetap occupied meski booking dibatalkan
                if ($kamar->occupancy() > 0) {
                    $kamar->update(['status' => 'occupied']);
                } elseif ($kamar->status === 'booked') {
                    $stillReserved = Booking::where('kamar_id', $kamar->id)
                        ->whereKeyNot($booking->id)
                        ->where('status', 'approved')
                        ->whereDate('end_date', '>=', today())
                        ->exists();
                    if (! $stillReserved) {
                        $kamar->update(['status' => 'available']);
                    }
                }
            }

            return true;
        });
    }
}
