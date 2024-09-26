<?php

namespace ApproTickets\Policies;

use ApproTickets\Models\User;
use ApproTickets\Models\Product;

class ProductPolicy
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
        return $user->hasRole('organizer') && $product->user_id == $user->id;
    }

    public function delete(User $user, Product $product): bool
    {
        return false;
    }

}