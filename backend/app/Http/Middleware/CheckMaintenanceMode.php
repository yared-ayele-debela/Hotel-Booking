<?php

namespace App\Http\Middleware;

use App\Services\WebsiteSettingsService;
use Closure;
use Illuminate\Http\Request;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        if (WebsiteSettingsService::isMaintenanceMode()) {
            // Allow access to admin panel and login pages
            $allowedRoutes = [
                'admin.*',
                'login',
                'logout',
                'password.*',
                'register',
            ];

            foreach ($allowedRoutes as $route) {
                if ($request->is($route)) {
                    return $next($request);
                }
            }

            // If it's an AJAX request, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => WebsiteSettingsService::getMaintenanceMessage(),
                    'maintenance_mode' => true
                ], 503);
            }

            // Show maintenance page
            return response()->view('maintenance', [
                'message' => WebsiteSettingsService::getMaintenanceMessage()
            ], 503);
        }

        return $next($request);
    }
}
