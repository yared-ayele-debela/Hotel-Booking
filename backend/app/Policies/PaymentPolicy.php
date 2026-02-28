<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Payment;
use App\Models\User;
use App\Policies\Concerns\HandlesVendorIsolation;

class PaymentPolicy
{
    use HandlesVendorIsolation;

    public function viewAny(User $user): bool
    {
        return in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN, Role::VENDOR], true);
    }

    public function view(User $user, Payment $payment): bool
    {
        if (in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN], true)) {
            return true;
        }

        return $this->canAccessVendorResource($user, $payment);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN, Role::VENDOR], true);
    }

    public function update(User $user, Payment $payment): bool
    {
        return $this->view($user, $payment);
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $this->view($user, $payment);
    }
}

