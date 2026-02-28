<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\User;
use App\Policies\Concerns\HandlesVendorIsolation;

class BookingPolicy
{
    use HandlesVendorIsolation;

    public function viewAny(User $user): bool
    {
        return in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN, Role::VENDOR, Role::CUSTOMER], true);
    }

    public function view(User $user, Booking $booking): bool
    {
        if (in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN], true)) {
            return true;
        }

        if ($user->role === Role::VENDOR) {
            return isset($user->vendor_id, $booking->vendor_id)
                && $user->vendor_id === $booking->vendor_id;
        }

        if ($user->role === Role::CUSTOMER) {
            return $booking->customer_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN, Role::VENDOR, Role::CUSTOMER], true);
    }

    public function update(User $user, Booking $booking): bool
    {
        return $this->view($user, $booking);
    }

    public function delete(User $user, Booking $booking): bool
    {
        return $this->view($user, $booking);
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $this->view($user, $booking);
    }
}

