<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function view(User $user, Message $message): bool
    {
        return (int) $message->sender_id === (int) $user->id
            || (int) $message->receiver_id === (int) $user->id;
    }

    /**
     * Tenant boleh chat ke owner kos penghuniannya.
     * Owner boleh membalas pesan tenant kos miliknya.
     * Admin boleh chat ke owner dari kos assigned (komunikasi operasional).
     * Admin TIDAK mendapat Owner Management — hanya communication.
     */
    public function send(User $user): bool
    {
        return $user->isTenant() || $user->isOwner() || $user->isAdmin();
    }

    /**
     * Edit milik pengirim sendiri. Batasan "belum dibalas owner"
     * ditegakkan di controller (state-dependent).
     */
    public function update(User $user, Message $message): bool
    {
        return (int) $message->sender_id === (int) $user->id;
    }
}
