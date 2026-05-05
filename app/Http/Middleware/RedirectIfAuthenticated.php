<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Redirect pengguna yang sudah login ke dashboard yang sesuai.
     * Admin (guard 'user') → dashboardadmin, Siswa (guard 'siswa') → dashboard.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        // Cek admin terlebih dahulu
        if (Auth::guard('user')->check()) {
            return redirect()->route('dashboardadmin');
        }

        // Cek siswa
        if (Auth::guard('siswa')->check()) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
