<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnlyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            abort(403);
        }
        $user = auth()->user();
        if (! in_array($user->role, [Role::SUPER_ADMIN, Role::ADMIN], true)) {
            abort(403, 'This area is for administrators only.');
        }
        return $next($request);
    }
}
