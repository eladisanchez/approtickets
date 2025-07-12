<?php

namespace ApproTickets\Policies;

use ApproTickets\Models\User;
use ApproTickets\Models\Booking;

class BookingPolicy
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
        // Consultar protecció de dades
        // return true;
    }

    public function view(User $user, Booking $booking): bool
    {
        return false;
        // $products = $user->products()->pluck('id')->toArray();
        // return in_array($booking->product_id, $products);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Booking $booking): bool
    {
        return false;
    }

}