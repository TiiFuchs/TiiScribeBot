<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccessPolicy
{

    use HandlesAuthorization;

    public function canAccess(User $user): bool
    {
        return $user->can_access;
    }

    public function canInvite(User $user): bool
    {
        return $user->can_invite;
    }

}
