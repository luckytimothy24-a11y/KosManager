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
     * Tenant hanya boleh mengirim ke owner kos tempat ia berpenghuni aktif.
     * Owner hanya boleh membalas pesan tenant yang berasal dari kos miliknya.
     */
    public function send(User $user): bool
    {
        return $user->isTenant() || $user->isOwner();
    }
}
