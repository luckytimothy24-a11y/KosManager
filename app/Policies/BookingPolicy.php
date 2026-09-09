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
        return true;
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

    public function update(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isOwner();
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $booking->user_id === $user->id
            && in_array($booking->status, Booking::activeStatuses());
    }

    private function isKosOwner(User $user, $model): bool
    {
        return $model->kos->owner_id === $user->id;
    }
}
