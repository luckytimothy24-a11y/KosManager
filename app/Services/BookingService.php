<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingOccupant;
use App\Models\Kamar;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BookingService
{
    /**
     * Buat booking baru.
     *
     * Business rules:
     * - transaksi + lockForUpdate pada kamar untuk mencegah race condition
     * - kamar harus berstatus available & kos aktif
     * - deteksi overlap terhadap booking pending/approved (reservasi) pada periode yang sama
     * - harga diambil dari harga existing (harian/bulanan)
     * - booking SELALU dibuat berstatus pending — TIDAK boleh auto-confirm
     * - SATU KAMAR UNTUK SATU PENGHUNI (patungan/multi-occupant DIHAPUS)
     * - kamar di-set booked (reservasi, bukan occupied)
     * - tenant TIDAK otomatis aktif (status tenant tidak disentuh di sini)
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

            // HARD RULE: selalu 1 orang, personal — patungan/multi dihapus
            $requestedOccupants = 1;

            // Status yang mengunci slot kamar: pending + approved (reservasi aktif).
            $reserving = Booking::roomReservingStatuses();

            // 1 kamar = 1 penghuni: kamar harus available + tidak ada overlap
            if ($kamar->status !== 'available') {
                return ['ok' => false, 'error' => 'kamar_tidak_tersedia'];
            }
            if ((int) ($kamar->capacity ?? 1) > 1) {
                // Kamar lama shared — setelah backfill harusnya 1; tetap tolak multi booking
                $occupancy = $kamar->occupancy();
                if ($occupancy >= 1) {
                    return ['ok' => false, 'error' => 'kamar_penuh', 'capacity' => 1, 'remaining' => 0];
                }
            }
            $hasConflict = Booking::where('kamar_id', $kamar->id)
                ->whereIn('status', $reserving)
                ->where('start_date', '<', $payload['end_date'])
                ->where('end_date', '>', $payload['start_date'])
                ->exists();
            if ($hasConflict) {
                return ['ok' => false, 'error' => 'overlap'];
            }

            // Selalu personal — patungan dihapus
            $billingMode = 'personal';
            $requestedBillingMode = $payload['billing_mode'] ?? 'personal';
            if ($requestedBillingMode === 'shared') {
                return ['ok' => false, 'error' => 'billing_mode_not_allowed'];
            }
            if ((int) ($payload['requested_occupants'] ?? 1) > 1) {
                return ['ok' => false, 'error' => 'personal_multi_rejected'];
            }

            $price = match ($payload['rental_type']) {
                'daily' => $kamar->daily_price,
                'weekly' => $kamar->weekly_price,
                'monthly' => $kamar->monthly_price,
                default => $kamar->monthly_price,
            };

            if (! $price) {
                return ['ok' => false, 'error' => 'no_price'];
            }

            // STATUS SELALU pending — jangan percaya status dari frontend.
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
                'billing_mode' => $billingMode,
                'requested_occupants' => $requestedOccupants,
                'notes' => $payload['notes'] ?? null,
                'identity_number' => trim($payload['identity_number'] ?? ''),
                'phone' => $payload['phone'] ?? '',
                'address' => trim($payload['address'] ?? ''),
                'identity_data_submitted_at' => now(),
                'status' => Booking::STATUS_PENDING,
            ]);

            // Ensure primary booking occupant exists for group flow
            try {
                if (Schema::hasTable('booking_occupants')) {
                    $user = User::find($userId);
                    BookingOccupant::firstOrCreate(
                        ['booking_id' => $booking->id, 'occupant_index' => 1],
                        [
                            'user_id' => $userId,
                            'email' => strtolower($user?->email ?? ''),
                            'display_name' => $user?->name ?? null,
                            'status' => BookingOccupant::STATUS_ACCEPTED,
                            'identity_status' => BookingOccupant::IDENTITY_PENDING,
                            'identity_number' => trim($payload['identity_number'] ?? ''),
                            'phone' => $payload['phone'] ?? '',
                            'address' => trim($payload['address'] ?? ''),
                            'identity_data_submitted_at' => now(),
                        ]
                    );
                }
            } catch (\Throwable $e) {
            }

            // 1 kamar = 1 penghuni: reservasi = booked (bukan occupied)
            $kamar->update(['status' => 'booked']);

            return ['ok' => true, 'booking' => $booking];
        });

        if (($result['ok'] ?? false) && isset($result['booking'])) {
            $booking = $result['booking'];
            $booking->load(['user', 'kos', 'kamar']);

            try {
                // Booking baru = pending. Bukan "instant confirmation".
                NotificationService::bookingCreated(
                    $booking->user_id,
                    $booking->booking_code,
                    $booking->kamar->room_number,
                    url('/tenant/booking/'.$booking->id)
                );
                NotificationService::bookingNew(
                    $booking->kos->owner_id,
                    $booking->user->name,
                    $booking->kamar->room_number,
                    url('/owner/booking/'.$booking->id)
                );
                AuditLogService::create(
                    'Booking',
                    "Booking baru {$booking->user->name} untuk kamar {$booking->kamar->room_number} (status pending, menunggu konfirmasi)",
                    ['kos_id' => $booking->kos_id, 'kamar_id' => $booking->kamar_id, 'status' => $booking->status]
                );

                app(AdvertisingService::class)->recordConversionIfApplicable($userId, $booking->kos_id);
            } catch (\Exception $e) {
                \Log::warning('Booking notification/audit failed: '.$e->getMessage());
            }
        }

        return $result;
    }

    /**
     * FINAL cancel booking — hanya untuk owner/admin.
     * Tenant request cancellation melalui BookingStatusService::requestCancellation()
     * (booking.status tidak berubah sampai owner approve).
     *
     * @return bool true bila booking berpindah ke cancelled
     */
    public function cancel(int $bookingId, ?User $actor = null): bool
    {
        $booking = Booking::find($bookingId);

        if (! $booking || ! $actor) {
            return false;
        }

        // Tenant tidak boleh membatalkan booking secara langsung.
        if ((int) $booking->user_id === (int) $actor->id && ! $actor->isOwner() && ! $actor->isAdmin() && ! $actor->isSuperAdmin()) {
            return false;
        }

        $result = app(BookingStatusService::class)->cancel($booking, $actor);

        return (bool) ($result['ok'] ?? false);
    }
}
