<?php

namespace ApproTickets\Policies;

use ApproTickets\Models\User;
use ApproTickets\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductPolicy
{

    public function before(User $user, string $ability): bool|null
    {
        return Cache::remember("user_is_admin", 600, function () use ($user) {
            return $user->hasRole('admin') ? true : null;     
        });
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        return $user->hasRole('organizer') && $product->user_id == $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('organizer');
    }

    public function update(User $user, Product $product): bool
    {
        return false;
        //return $user->hasRole('organizer') && $product->user_id == $user->id;
    }

    public function delete(User $user, Product $product): bool
    {
        return false;
    }

}