<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Redirect unauthenticated users ke halaman login yang sesuai.
     * Guard 'user' → /panel (admin), selainnya → / (siswa).
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // Periksa middleware yang terdaftar di route saat ini
        $routeMiddleware = $request->route()?->gatherMiddleware() ?? [];

        foreach ($routeMiddleware as $mw) {
            if (is_string($mw) && str_starts_with($mw, 'auth:user')) {
                return route('loginadmin');
            }
        }

        return route('login');
    }
}
