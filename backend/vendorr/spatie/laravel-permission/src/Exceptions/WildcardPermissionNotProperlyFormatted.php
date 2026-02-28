<?php

namespace Spatie\Permission\Exceptions;

use InvalidArgumentException;

class WildcardPermissionNotProperlyFormatted extends InvalidArgumentException
{
    public static function create(string $permission)
    {
        return new static(__('Wildcard permissions `:permissions` is not properly formatted.', [
            'permissions' => $permission,
        ]));
    }
}
