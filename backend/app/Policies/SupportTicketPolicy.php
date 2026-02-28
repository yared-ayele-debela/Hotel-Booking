<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN], true);
    }

    public function view(User $user, SupportTicket $ticket): bool
    {
        return in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN], true);
    }

    public function update(User $user, SupportTicket $ticket): bool
    {
        return in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN], true);
    }
}
