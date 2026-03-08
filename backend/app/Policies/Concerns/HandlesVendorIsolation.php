<?php

namespace App\Policies\Concerns;

use App\Enums\Role;
use App\Models\User;

trait HandlesVendorIsolation
{
    protected function canAccessVendorResource(User $user, object $resource): bool
    {
        return match ($user->role) {
            Role::SUPER_ADMIN, Role::ADMIN => true,
            Role::VENDOR => $user->isVendorApproved()
                && (! isset($resource->vendor_id) || $resource->vendor_id === $user->id),
            default => false,
        };
    }
}

