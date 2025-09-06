<?php

namespace ApproTickets\Policies;

use ApproTickets\Models\User;
use ApproTickets\Models\Booking;
use Illuminate\Support\Facades\Cache;

class BookingPolicy
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