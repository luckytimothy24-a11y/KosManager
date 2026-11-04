<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingCancellationRequest;
use App\Models\Kamar;
use App\Models\Penghuni;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * SATU service terkontrol untuk transisi status booking + cancellation request.
 *
 * Prinsip:
 * - Frontend hanya mengirim ACTION.
 * - Backend menentukan status final via state machine Booking.
 * - Tenant Request Cancellation ≠ Booking Cancelled.
 * - Hanya Owner approveCancellation() yang membatalkan booking.
 * - Tidak boleh mengubah status tenant/payment secara implisit.
 */
class BookingStatusService
{
    /**
     * Approve (confirm) booking — hanya owner/admin berwenang.
     * PREREQUISITE: payment booking harus berstatus approved.
     * Payment approved ≠ booking approved — konfirmasi tetap action terpisah.
     *
     * @return array{ok: bool, booking?: Booking, error?: string}
     */
    public function approve(Booking $booking, User $actor): array
    {
        $result = $this->transition($booking, $actor, Booking::STATUS_APPROVED, 'approve');

        // Defense-in-depth: meski state machine lolos, payment prerequisite wajib
        // dan nominalnya harus sesuai DP/harga booking yang ditentukan server.
        if (($result['ok'] ?? false) && isset($result['booking']) && ! $result['booking']->hasApprovedPaymentInExpectedAmount()) {
            return ['ok' => false, 'error' => 'payment_required'];
        }

        return $result;
    }

    /**
     * Reject booking oleh owner/admin.
     *
     * @return array{ok: bool, booking?: Booking, error?: string}
     */
    public function reject(Booking $booking, User $actor, ?string $reason = null): array
    {
        $result = $this->transition($booking, $actor, Booking::STATUS_REJECTED, 'reject');

        if (($result['ok'] ?? false) && $reason !== null && isset($result['booking'])) {
            $result['booking']->updateQuietly([
                'identity_rejected_at' => null,
                'identity_confirmed_at' => null,
            ]);
            $result['reason'] = $reason;
        }

        return $result;
    }

    /**
     * FINAL cancel booking — hanya dipanggil dari approveCancellation()
     * (owner approval). Jangan dipanggil langsung dari tenant request.
     *
     * @return array{ok: bool, booking?: Booking, error?: string}
     */
    public function cancel(Booking $booking, User $actor): array
    {
        return $this->transition($booking, $actor, Booking::STATUS_CANCELLED, 'cancel');
    }

    /**
     * Complete booking — hanya setelah masa sewa selesai / checkout flow.
     *
     * @return array{ok: bool, booking?: Booking, error?: string}
     */
    public function complete(Booking $booking, User $actor): array
    {
        return $this->transition($booking, $actor, Booking::STATUS_COMPLETED, 'complete');
    }

    /**
     * TENANT: request pembatalan booking.
     *
     * booking.status TIDAK berubah.
     * tenant.status TIDAK berubah.
     * payment TIDAK berubah.
     * room reservation TIDAK berubah.
     *
     * @return array{ok: bool, request?: BookingCancellationRequest, error?: string}
     */
    public function requestCancellation(Booking $booking, User $actor, ?string $reason = null): array
    {
        return (array) DB::transaction(function () use ($booking, $actor, $reason) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->first();

            if (! $locked) {
                return ['ok' => false, 'error' => 'not_found'];
            }

            if ((int) $locked->user_id !== (int) $actor->id) {
                return ['ok' => false, 'error' => 'forbidden'];
            }

            if (! in_array($locked->status, Booking::cancellableStatuses(), true)) {
                return ['ok' => false, 'error' => 'not_cancellable'];
            }

            $existingPending = BookingCancellationRequest::where('booking_id', $locked->id)
                ->where('status', BookingCancellationRequest::STATUS_PENDING)
                ->exists();

            if ($existingPending) {
                return ['ok' => false, 'error' => 'already_requested'];
            }

            $request = BookingCancellationRequest::create([
                'booking_id' => $locked->id,
                'requested_by' => $actor->id,
                'status' => BookingCancellationRequest::STATUS_PENDING,
                'reason' => $reason !== null && trim($reason) !== '' ? trim($reason) : null,
            ]);

            // Booking status SENGaja TIDAK diubah. Reservation TIDAK dilepas.
            return [
                'ok' => true,
                'request' => $request,
                'booking' => $locked->fresh(['user', 'kos', 'kamar']),
            ];
        });
    }

    /**
     * OWNER: approve cancellation request → booking → cancelled.
     * Atomic: request approved + booking cancelled + release reservation + audit.
     *
     * @return array{ok: bool, request?: BookingCancellationRequest, booking?: Booking, error?: string}
     */
    public function approveCancellation(BookingCancellationRequest $request, User $actor): array
    {
        return (array) DB::transaction(function () use ($request, $actor) {
            $lockedRequest = BookingCancellationRequest::whereKey($request->id)->lockForUpdate()->first();

            if (! $lockedRequest || ! $lockedRequest->isPending()) {
                return ['ok' => false, 'error' => 'request_not_pending'];
            }

            $booking = Booking::whereKey($lockedRequest->booking_id)->lockForUpdate()->first();

            if (! $booking) {
                return ['ok' => false, 'error' => 'not_found'];
            }

            if (! $this->isOwnerOrAdminOfKos($actor, $booking)) {
                return ['ok' => false, 'error' => 'forbidden'];
            }

            if (! $booking->canTransitionFrom((string) $booking->status, Booking::STATUS_CANCELLED)) {
                return ['ok' => false, 'error' => 'invalid_transition'];
            }

            // Final transition booking → cancelled (satu jalur terkontrol).
            $booking->update(['status' => Booking::STATUS_CANCELLED]);

            // DP non-refundable: hak owner langsung siap dicairkan.
            $this->releaseDownPaymentDistribution($booking);

            // Distribution tidak otomatis processed saat cancel — biarkan pending/failed oleh policy bisnis.
            // Owner amount hanya "hak" setelah booking confirmed; untuk booking DP yang terminal,
            // DP tetap menjadi hak owner (non-refundable).

            $lockedRequest->update([
                'status' => BookingCancellationRequest::STATUS_APPROVED,
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);

            $this->syncRoomAfterBookingTransition($booking, Booking::STATUS_CANCELLED);

            // Refund: mark approved payments as refund requested
            $this->requestRefundForCancelledBooking($booking, $actor);

            return [
                'ok' => true,
                'request' => $lockedRequest->fresh(),
                'booking' => $booking->fresh(['user', 'kos', 'kamar']),
            ];
        });
    }

    /**
     * OWNER: reject cancellation request.
     * booking.status TIDAK berubah.
     *
     * @return array{ok: bool, request?: BookingCancellationRequest, booking?: Booking, error?: string}
     */
    public function rejectCancellation(BookingCancellationRequest $request, User $actor, ?string $reason = null): array
    {
        return (array) DB::transaction(function () use ($request, $actor, $reason) {
            $lockedRequest = BookingCancellationRequest::whereKey($request->id)->lockForUpdate()->first();

            if (! $lockedRequest || ! $lockedRequest->isPending()) {
                return ['ok' => false, 'error' => 'request_not_pending'];
            }

            $booking = Booking::whereKey($lockedRequest->booking_id)->lockForUpdate()->first();

            if (! $booking) {
                return ['ok' => false, 'error' => 'not_found'];
            }

            if (! $this->isOwnerOrAdminOfKos($actor, $booking)) {
                return ['ok' => false, 'error' => 'forbidden'];
            }

            $lockedRequest->update([
                'status' => BookingCancellationRequest::STATUS_REJECTED,
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason !== null && trim($reason) !== '' ? trim($reason) : null,
            ]);

            // Booking status TIDAK berubah.
            return [
                'ok' => true,
                'request' => $lockedRequest->fresh(),
                'booking' => $booking->fresh(['user', 'kos', 'kamar']),
            ];
        });
    }

    /**
     * Transisi terpusat: VALIDATE → AUTHORIZE → TRANSITION → room side-effect.
     *
     * @return array{ok: bool, booking?: Booking, error?: string}
     */
    private function transition(Booking $booking, User $actor, string $target, string $action): array
    {
        return (array) DB::transaction(function () use ($booking, $actor, $target, $action) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->first();

            if (! $locked) {
                return ['ok' => false, 'error' => 'not_found'];
            }

            if (! $locked->canTransitionFrom((string) $locked->status, $target)) {
                return ['ok' => false, 'error' => 'invalid_transition'];
            }

            // Payment prerequisite untuk konfirmasi booking. Nominal harus sesuai
            // DP booking baru atau harga penuh booking legacy.
            if ($target === Booking::STATUS_APPROVED && ! $locked->hasApprovedPaymentInExpectedAmount()) {
                return ['ok' => false, 'error' => 'payment_required'];
            }

            if (! $this->actorCanPerform($actor, $locked, $action)) {
                return ['ok' => false, 'error' => 'forbidden'];
            }

            $locked->update(['status' => $target]);

            // Side effect HANYA pada reservasi kamar — tidak pernah status tenant.
            $this->syncRoomAfterBookingTransition($locked, $target);

            // Booking confirmed → distribution siap (ready), bukan processed.
            if ($target === Booking::STATUS_APPROVED) {
                try {
                    app(PaymentDistributionService::class)->markReadyForBooking($locked);
                } catch (\Throwable $e) {
                    \Log::warning('Mark distribution ready failed: '.$e->getMessage());
                }
            }

            // Booking terminal dengan DP → DP non-refundable menjadi hak owner.
            if (in_array($target, [Booking::STATUS_REJECTED, Booking::STATUS_CANCELLED, Booking::STATUS_EXPIRED], true)) {
                $this->releaseDownPaymentDistribution($locked);
            }

            return ['ok' => true, 'booking' => $locked->fresh(['user', 'kos', 'kamar']), 'action' => $action];
        });
    }

/**
     * Tandai pembayaran yang sudah approved sebagai refund requested
     * saat booking dibatalkan oleh owner.
     */
    private function requestRefundForCancelledBooking(Booking $booking, User $actor): void
    {
        $pembayarans = \App\Models\Pembayaran::where('booking_id', $booking->id)
            ->where('verification_status', 'approved')
            ->where('refund_status', 'none')
            ->get();

        foreach ($pembayarans as $pembayaran) {
            $pembayaran->update([
                'refund_status' => \App\Models\Pembayaran::REFUND_REQUESTED,
                'refund_amount' => $pembayaran->amount,
                'refund_reason' => 'Booking dibatalkan oleh owner: ' . ($booking->booking_code ?? ''),
            ]);

            // Notifikasi ke tenant
            try {
                \App\Services\NotificationService::create(
                    $booking->user_id,
                    'refund',
                    'Refund Diminta',
                    "Booking {$booking->booking_code} dibatalkan. Refund sebesar Rp " . number_format($pembayaran->amount, 0, ',', '.') . " akan diproses.",
                    ['url' => route('tenant.pembayaran.show', $pembayaran)],
                    "refund:{$pembayaran->id}"
                );
            } catch (\Exception $e) {
                \Log::warning("Refund notification failed for pembayaran {$pembayaran->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Lepaskan distribusi DP ke owner saat booking terminal.
     * Hanya berlaku untuk booking baru yang memiliki DP; booking legacy tidak diubah.
     */
    private function releaseDownPaymentDistribution(Booking $booking): void
    {
        if (! $booking->usesDownPayment()) {
            return;
        }

        try {
            app(PaymentDistributionService::class)->markReadyForBooking($booking);
        } catch (\Throwable $e) {
            \Log::warning('Release DP distribution failed: '.$e->getMessage());
        }
    }

    /**
     * Authorization defense-in-depth (policy tetap wajib dipanggil di controller).
     * Tenant TIDAK boleh memanggil cancel() final — hanya requestCancellation().
     */
    private function actorCanPerform(User $actor, Booking $booking, string $action): bool
    {
        if ($action === 'cancel') {
            // Final cancel hanya untuk owner/admin kos terkait (dipanggil dari approveCancellation).
            return $this->isOwnerOrAdminOfKos($actor, $booking);
        }

        // approve/reject/complete: hanya owner/admin/super_admin kos terkait.
        return $this->isOwnerOrAdminOfKos($actor, $booking);
    }

    private function isOwnerOrAdminOfKos(User $actor, Booking $booking): bool
    {
        if ($actor->isSuperAdmin()) {
            return true;
        }

        $kos = $booking->kos;

        if ($actor->isOwner() && $kos && (int) $kos->owner_id === (int) $actor->id) {
            return true;
        }

        if ($actor->isAdmin() && method_exists($actor, 'canAccessKos') && $actor->canAccessKos((int) $booking->kos_id)) {
            return true;
        }

        return false;
    }

    /**
     * Sinkron status kamar setelah transisi booking.
     * Kamar occupied hanya jika ada penghuni aktif — booking approved saja tidak cukup.
     */
    private function syncRoomAfterBookingTransition(Booking $booking, string $target): void
    {
        $kamar = Kamar::whereKey($booking->kamar_id)->lockForUpdate()->first();

        if (! $kamar) {
            return;
        }

        $kamar->syncStatus();
    }
}
