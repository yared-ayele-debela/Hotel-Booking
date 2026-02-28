<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN, Role::VENDOR], true);
    }

    public function view(User $user, Room $room): bool
    {
        if (in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN], true)) {
            return true;
        }
        return $user->role === Role::VENDOR && $room->hotel && $room->hotel->vendor_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN, Role::VENDOR], true);
    }

    public function update(User $user, Room $room): bool
    {
        return $this->view($user, $room);
    }

    public function delete(User $user, Room $room): bool
    {
        return $this->view($user, $room);
    }
}

