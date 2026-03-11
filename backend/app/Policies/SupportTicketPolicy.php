<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN, Role::VENDOR, Role::CUSTOMER], true);
    }

    public function view(User $user, SupportTicket $ticket): bool
    {
        if (in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN], true)) {
            return true;
        }
        return (int) $ticket->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [Role::VENDOR, Role::CUSTOMER], true);
    }

    public function update(User $user, SupportTicket $ticket): bool
    {
        return in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN], true);
    }

    public function reply(User $user, SupportTicket $ticket): bool
    {
        return (int) $ticket->user_id === (int) $user->id;
    }
}
