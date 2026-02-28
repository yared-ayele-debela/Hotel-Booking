<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Review;
use App\Models\User;
use App\Policies\Concerns\HandlesVendorIsolation;

class ReviewPolicy
{
    use HandlesVendorIsolation;

    public function viewAny(User $user): bool
    {
        return in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN, Role::VENDOR, Role::CUSTOMER], true);
    }

    public function view(User $user, Review $review): bool
    {
        if (in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN], true)) {
            return true;
        }

        if ($user->role === Role::VENDOR) {
            return $this->canAccessVendorResource($user, $review);
        }

        if ($user->role === Role::CUSTOMER) {
            return isset($review->booking) && $review->booking->customer_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === Role::CUSTOMER;
    }

    public function update(User $user, Review $review): bool
    {
        return $this->view($user, $review);
    }

    public function delete(User $user, Review $review): bool
    {
        return in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN], true);
    }
}

