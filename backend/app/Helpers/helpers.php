<?php
function isAdmin()
{
    return auth()->check() && auth()->user()->hasRole('admin|super-admin');
}
