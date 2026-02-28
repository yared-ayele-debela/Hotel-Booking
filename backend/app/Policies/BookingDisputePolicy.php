<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\BookingDispute;
use App\Models\User;

class BookingDisputePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN], true);
    }

    public function view(User $user, BookingDispute $dispute): bool
    {
        return in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN], true);
    }

    public function update(User $user, BookingDispute $dispute): bool
    {
        return in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN], true);
    }
}
