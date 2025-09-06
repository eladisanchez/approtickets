<?php

namespace ApproTickets\Policies;

use ApproTickets\Models\User;

class ExtractPolicy
{

    public function before(User $user, string $ability): bool|null
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

}