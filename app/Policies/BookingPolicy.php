<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner() || $user->isTenant();
    }

    public function view(User $user, Booking $booking): bool
    {
        return $user->isSuperAdmin()
            || ($user->isAdmin() && $user->canAccessKos((int) $booking->kos_id))
            || $this->isKosOwner($user, $booking)
            || $booking->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isTenant();
    }

    /**
     * Update umum oleh owner/admin/super_admin (data non-status).
     * Transisi status harus melalui approve/reject/cancel + BookingStatusService.
     */
    public function update(User $user, Booking $booking): bool
    {
        return $this->isOwnerOrAdminOfKos($user, $booking);
    }

    /**
     * Owner/admin hanya boleh mengonfirmasi booking berstatus pending.
     * Prerequisite payment dicek di BookingStatusService (payment_required).
     * Tenant TIDAK boleh memanggil approve.
     */
    public function approve(User $user, Booking $booking): bool
    {
        return $this->isOwnerOrAdminOfKos($user, $booking)
            && $booking->status === Booking::STATUS_PENDING;
    }

    /**
     * Owner/admin menolak booking pending.
     * Hanya booking dengan status pending yang bisa ditolak menjadi rejected.
     * Status approved tidak bisa langsung direject — harus melalui transisi state machine.
     */
    public function reject(User $user, Booking $booking): bool
    {
        return $this->isOwnerOrAdminOfKos($user, $booking)
            && $booking->status === Booking::STATUS_PENDING;
    }

    /**
     * Tenant pemilik boleh MEMINTA pembatalan (request) selama status masih pending/approved.
     * Ini TIDAK langsung membatalkan booking — hanya membuat cancellation request.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        return (int) $booking->user_id === (int) $user->id
            && in_array($booking->status, Booking::cancellableStatuses(), true);
    }

    /**
     * Owner/admin menyetujui cancellation request (final cancel booking).
     */
    public function approveCancellation(User $user, Booking $booking): bool
    {
        return $this->isOwnerOrAdminOfKos($user, $booking);
    }

    /**
     * Owner/admin menolak cancellation request — booking tidak berubah.
     */
    public function rejectCancellation(User $user, Booking $booking): bool
    {
        return $this->approveCancellation($user, $booking);
    }

    private function isKosOwner(User $user, $model): bool
    {
        // Fail-closed bila relasi kos terhapus: tolak alih-alih error 500.
        $ownerId = $model->kos?->owner_id;

        return $ownerId !== null && (int) $ownerId === (int) $user->id;
    }

    private function isOwnerOrAdminOfKos(User $user, Booking $booking): bool
    {
        return $user->isSuperAdmin()
            || ($user->isAdmin() && $user->canAccessKos((int) $booking->kos_id))
            || $this->isKosOwner($user, $booking);
    }
}
