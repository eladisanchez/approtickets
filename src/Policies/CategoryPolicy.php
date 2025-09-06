<?php

namespace ApproTickets\Policies;

use ApproTickets\Models\User;
use Illuminate\Support\Facades\Cache;

class CategoryPolicy
{

    public function before(User $user, string $ability): bool|null
    {
        return Cache::remember("user_is_admin", 600, function () use ($user) {
            return $user->hasRole('admin') ? true : null;     
        });
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

}