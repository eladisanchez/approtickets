<?php

namespace ApproTickets\Policies;

use ApproTickets\Models\User;
use ApproTickets\Models\Option;

class OptionPolicy
{

    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

}