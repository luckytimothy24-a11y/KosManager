<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\BookingCancellationRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingCancellationRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Tenant pemilik booking boleh request cancellation
     * selama booking masih pending/approved.
     */
    public function create(User $user, Booking $booking): bool
    {
        return (int) $booking->user_id === (int) $user->id
            && in_array($booking->status, Booking::cancellableStatuses(), true);
    }

    /**
     * Hanya owner/admin kos terkait yang boleh menyetujui/tolak request.
     * Tenant TIDAK boleh approve request miliknya sendiri.
     */
    public function approve(User $user, BookingCancellationRequest $request): bool
    {
        return $this->canReview($user, $request) && $request->isPending();
    }

    public function reject(User $user, BookingCancellationRequest $request): bool
    {
        return $this->approve($user, $request);
    }

    public function view(User $user, BookingCancellationRequest $request): bool
    {
        $booking = $request->booking;
        if (! $booking) {
            return false;
        }

        return $user->isSuperAdmin()
            || (int) $booking->user_id === (int) $user->id
            || $this->canReview($user, $request);
    }

    private function canReview(User $user, BookingCancellationRequest $request): bool
    {
        $booking = $request->booking;
        if (! $booking) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        $kos = $booking->kos;

        if ($user->isOwner() && $kos && (int) $kos->owner_id === (int) $user->id) {
            return true;
        }

        if ($user->isAdmin() && method_exists($user, 'canAccessKos') && $user->canAccessKos((int) $booking->kos_id)) {
            return true;
        }

        return false;
    }
}
