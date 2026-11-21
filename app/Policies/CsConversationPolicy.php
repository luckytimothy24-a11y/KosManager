<?php

namespace App\Policies;

use App\Models\CsConversation;
use App\Models\User;

class CsConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTenant() || $user->isAdmin() || $user->isSuperAdmin();
    }

    public function view(User $user, CsConversation $conversation): bool
    {
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        return $user->isTenant() && (int) $conversation->tenant_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isTenant();
    }

    public function reply(User $user, CsConversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }

    public function updateStatus(User $user, CsConversation $conversation): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }
}
