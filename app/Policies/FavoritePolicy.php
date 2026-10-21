<?php

namespace App\Policies;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FavoritePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isTenant();
    }

    public function create(User $user): bool
    {
        return $user->isTenant();
    }

    public function delete(User $user, Favorite $favorite): bool
    {
        return (int) $favorite->user_id === (int) $user->id;
    }
}
