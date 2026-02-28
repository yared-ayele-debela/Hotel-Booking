<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class PermissionAlreadyExists extends InvalidArgumentException
{
    public static function create(string $permissionName, string $guardName)
    {
        return new static(__('A `:permissions` permissions already exists for guard `:guard`.', [
            'permissions' => $permissionName,
            'guard' => $guardName,
        ]));
    }
}
