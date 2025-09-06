<?php

namespace ApproTickets\Policies;

use ApproTickets\Models\User;
use ApproTickets\Models\Ticket;
use ApproTickets\Models\Product;
use Illuminate\Support\Facades\Cache;

class TicketPolicy
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

    public function view(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('organizer') && $ticket->product->user_id == $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return false;
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return false;
    }

}