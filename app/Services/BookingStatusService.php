<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingCancellationRequest;
use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\User;
use App\Support\NotificationType;
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

            // Refund: mark approved non-DP payments as refund requested.
            $this->requestRefundForCancelledBooking($booking, $actor, $lockedRequest);

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
     * Tandai pembayaran non-DP yang sudah approved sebagai refund requested
     * saat booking dibatalkan. DP non-refundable tidak pernah masuk refund.
     */
    private function requestRefundForCancelledBooking(
        Booking $booking,
        User $actor,
        ?BookingCancellationRequest $cancellationRequest = null
    ): void {
        $pembayarans = Pembayaran::where('booking_id', $booking->id)
            ->where('verification_status', Pembayaran::STATUS_APPROVED)
            ->where('refund_status', Pembayaran::REFUND_NONE)
            ->get();

        if ($booking->usesDownPayment()) {
            $downPaymentAmount = round((float) $booking->down_payment_amount, 2);

            $downPayments = $pembayarans->filter(
                fn (Pembayaran $pembayaran) => round((float) $pembayaran->amount, 2) === $downPaymentAmount
            );

            if ($downPayments->isNotEmpty()) {
                $this->notifyDownPaymentNonRefundable($booking, (float) $downPayments->sum('amount'));
            }

            // Pembayaran manual/extra pada booking ber-DP bukan DP dan bukan
            // alur legacy; jangan auto-refund, biarkan untuk review manual.
            $pembayarans
                ->reject(fn (Pembayaran $pembayaran) => round((float) $pembayaran->amount, 2) === $downPaymentAmount)
                ->each(function (Pembayaran $pembayaran) use ($booking) {
                    \Log::warning('Approved non-DP payment on DP booking left unchanged', [
                        'booking_id' => $booking->id,
                        'pembayaran_id' => $pembayaran->id,
                        'amount' => (float) $pembayaran->amount,
                    ]);
                });

            return;
        }

        foreach ($pembayarans as $pembayaran) {
            $pembayaran->update([
                'refund_status' => Pembayaran::REFUND_REQUESTED,
                'refund_amount' => $pembayaran->amount,
                'refund_reason' => $this->refundReasonForCancelledBooking($booking, $actor, $cancellationRequest),
            ]);

            // Notifikasi ke tenant
            try {
                NotificationService::create(
                    $booking->user_id,
                    'refund',
                    'Refund Booking Diajukan',
                    $this->refundNotificationForCancelledBooking($booking, $pembayaran, $actor, $cancellationRequest),
                    ['url' => route('tenant.pembayaran.show', $pembayaran)],
                    "refund:{$pembayaran->id}"
                );
            } catch (\Exception $e) {
                \Log::warning("Refund notification failed for pembayaran {$pembayaran->id}: {$e->getMessage()}");
            }
        }
    }

    private function notifyDownPaymentNonRefundable(Booking $booking, float $amount): void
    {
        try {
            NotificationService::create(
                $booking->user_id,
                NotificationType::BOOKING,
                'Booking Dibatalkan — DP Tidak Dapat Dikembalikan',
                "Booking {$booking->booking_code} telah dibatalkan. DP sebesar Rp ".number_format($amount, 0, ',', '.').' tidak dapat dikembalikan karena bersifat non-refundable dan menjadi hak pemilik kos sesuai ketentuan.',
                null,
                "dp-nonrefundable:{$booking->id}"
            );
        } catch (\Exception $e) {
            \Log::warning("DP non-refundable notification failed for booking {$booking->id}: {$e->getMessage()}");
        }
    }

    private function refundReasonForCancelledBooking(
        Booking $booking,
        User $actor,
        ?BookingCancellationRequest $cancellationRequest
    ): string {
        $code = $booking->booking_code ?? (string) $booking->id;

        if ($cancellationRequest && (int) $cancellationRequest->requested_by === (int) $booking->user_id) {
            return "Pengajuan pembatalan oleh penyewa disetujui: {$code}";
        }

        if ($actor->isOwner()) {
            return "Booking dibatalkan oleh owner: {$code}";
        }

        if ($actor->isAdmin() || $actor->isSuperAdmin()) {
            return "Booking dibatalkan oleh admin: {$code}";
        }

        return "Booking dibatalkan: {$code}";
    }

    private function refundNotificationForCancelledBooking(
        Booking $booking,
        Pembayaran $pembayaran,
        User $actor,
        ?BookingCancellationRequest $cancellationRequest
    ): string {
        $amount = number_format((float) $pembayaran->amount, 0, ',', '.');

        if ($cancellationRequest && (int) $cancellationRequest->requested_by === (int) $booking->user_id) {
            return "Booking {$booking->booking_code} dibatalkan atas permintaan penyewa. Refund sebesar Rp {$amount} akan diproses.";
        }

        if ($actor->isOwner()) {
            return "Booking {$booking->booking_code} dibatalkan oleh pemilik kos. Refund sebesar Rp {$amount} akan diproses.";
        }

        if ($actor->isAdmin() || $actor->isSuperAdmin()) {
            return "Booking {$booking->booking_code} dibatalkan oleh admin. Refund sebesar Rp {$amount} akan diproses.";
        }

        return "Booking {$booking->booking_code} dibatalkan. Refund sebesar Rp {$amount} akan diproses.";
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
