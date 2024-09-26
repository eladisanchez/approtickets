<?php

namespace ApproTickets\Policies;

use ApproTickets\Models\User;

class UserPolicy
{

    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return false;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

}