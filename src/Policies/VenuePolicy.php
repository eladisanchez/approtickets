<?php

namespace ApproTickets\Policies;

use ApproTickets\Models\User;
use Illuminate\Support\Facades\Cache;

class VenuePolicy
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