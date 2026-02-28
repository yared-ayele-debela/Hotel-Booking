<?php

namespace App\Policies;

use App\Models\Hotel;
use App\Models\User;
use App\Policies\Concerns\HandlesVendorIsolation;

class HotelPolicy
{
    use HandlesVendorIsolation;

    public function viewAny(User $user): bool
    {
        return $this->canAccessVendorResource($user, new Hotel());
    }

    public function view(User $user, Hotel $hotel): bool
    {
        return $this->canAccessVendorResource($user, $hotel);
    }

    public function create(User $user): bool
    {
        return $this->canAccessVendorResource($user, new Hotel());
    }

    public function update(User $user, Hotel $hotel): bool
    {
        return $this->canAccessVendorResource($user, $hotel);
    }

    public function delete(User $user, Hotel $hotel): bool
    {
        return $this->canAccessVendorResource($user, $hotel);
    }
}

