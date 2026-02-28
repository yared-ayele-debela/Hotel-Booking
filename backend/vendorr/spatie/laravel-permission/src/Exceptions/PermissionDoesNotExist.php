<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class PermissionDoesNotExist extends InvalidArgumentException
{
    public static function create(string $permissionName, ?string $guardName)
    {
        return new static(__('There is no permissions named `:permissions` for guard `:guard`.', [
            'permissions' => $permissionName,
            'guard' => $guardName,
        ]));
    }

    /**
     * @param  int|string  $permissionId
     * @return static
     */
    public static function withId($permissionId, ?string $guardName)
    {
        return new static(__('There is no [permissions] with ID `:id` for guard `:guard`.', [
            'id' => $permissionId,
            'guard' => $guardName,
        ]));
    }
}
