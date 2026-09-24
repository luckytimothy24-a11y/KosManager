<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\BookingOccupant;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingOccupantPolicy
{
    use HandlesAuthorization;

    public function view(User $user, BookingOccupant $occupant): bool
    {
        $booking = $occupant->booking;
        if (! $booking) return false;
        if ($user->isSuperAdmin()) return true;
        if ($user->id === (int) $booking->user_id) return true;
        if ((int) $occupant->user_id === (int) $user->id) return true;
        // Invitee by email (not yet bound to user_id) — allow if token matches via controller
        if ($occupant->email && strtolower($occupant->email) === strtolower($user->email)) return true;
        if ($user->isAdmin() && $user->canAccessKos((int) $booking->kos_id)) return true;
        if ($user->isOwner() && (int) $booking->kos->owner_id === (int) $user->id) return true;
        return false;
    }

    public function invite(User $user, Booking $booking): bool
    {
        return (int) $booking->user_id === (int) $user->id
            && in_array($booking->status, [Booking::STATUS_PENDING, Booking::STATUS_APPROVED], true);
    }

    public function cancel(User $user, BookingOccupant $occupant): bool
    {
        $booking = $occupant->booking;
        if (! $booking) return false;
        if ($occupant->occupant_index === 1) return false; // primary cannot be cancelled
        return (int) $booking->user_id === (int) $user->id
            && $occupant->status === BookingOccupant::STATUS_INVITED;
    }

    public function accept(User $user, BookingOccupant $occupant): bool
    {
        if ($occupant->status !== BookingOccupant::STATUS_INVITED) return false;
        if ($occupant->isInviteExpired()) return false;
        if (! $occupant->email) return false;
        return strtolower($occupant->email) === strtolower($user->email);
    }

    public function decline(User $user, BookingOccupant $occupant): bool
    {
        return $this->accept($user, $occupant);
    }

    public function updateIdentity(User $user, BookingOccupant $occupant): bool
    {
        if ((int) $occupant->user_id !== (int) $user->id) return false;
        if (! in_array($occupant->status, [BookingOccupant::STATUS_ACCEPTED, BookingOccupant::STATUS_INVITED], true)) {
            // Must be accepted to submit identity; invited alone not enough
            if ($occupant->status !== BookingOccupant::STATUS_ACCEPTED) return false;
        }
        return true;
    }

    public function confirmIdentity(User $user, BookingOccupant $occupant): bool
    {
        $booking = $occupant->booking;
        if (! $booking) return false;
        if (! in_array($user->role, ['owner', 'admin', 'super_admin'], true)) return false;
        if ($user->isOwner() && (int) $booking->kos->owner_id !== (int) $user->id) return false;
        if ($user->isAdmin() && ! $user->canAccessKos((int) $booking->kos_id)) return false;
        return $occupant->identity_status === BookingOccupant::IDENTITY_PENDING;
    }
}
