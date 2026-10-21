<?php

namespace App\Policies;

use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PembayaranPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner() || $user->isTenant();
    }

    public function view(User $user, Pembayaran $pembayaran): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($pembayaran->isBookingPayment()) {
            return $this->bookingScope($user, $pembayaran->booking);
        }

        if ($user->isAdmin()) {
            // Fail-closed bila penghuni terhapus: tolak alih-alih error 500.
            $kosId = $pembayaran->penghuni?->kos_id;

            return $kosId !== null && $user->canAccessKos((int) $kosId);
        }

        if ((int) ($pembayaran->penghuni?->user_id) === (int) $user->id) {
            return true;
        }

        $ownerId = $pembayaran->penghuni?->kamar?->kos?->owner_id;

        return $ownerId !== null && (int) $ownerId === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isTenant();
    }

    public function verify(User $user, Pembayaran $pembayaran): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Booking payment: scope owner/admin kos dari booking.
        if ($pembayaran->isBookingPayment()) {
            return $this->bookingScope($user, $pembayaran->booking);
        }

        if ($user->isAdmin()) {
            $kosId = $pembayaran->penghuni?->kos_id;

            return $kosId !== null && $user->canAccessKos((int) $kosId);
        }

        if ($user->isOwner()) {
            $kosOwnerId = $pembayaran->penghuni?->kamar?->kos?->owner_id
                ?? $pembayaran->penghuni?->kos?->owner_id
                ?? $pembayaran->booking?->kos?->owner_id;

            return $kosOwnerId !== null && (int) $kosOwnerId === (int) $user->id;
        }

        return false;
    }

    private function bookingScope(User $user, $booking): bool
    {
        if (! $booking) {
            return false;
        }

        if ((int) $booking->user_id === (int) $user->id) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->canAccessKos((int) $booking->kos_id);
        }

        $ownerId = $booking->kos?->owner_id;

        return $ownerId !== null && (int) $ownerId === (int) $user->id;
    }
}
