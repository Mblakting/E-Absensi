<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        // Tetapkan kedua guard untuk diperiksa, Admin didahulukan
        $allRelevantGuards = ['user', 'siswa']; 

        foreach ($allRelevantGuards as $guard) {
            if (Auth::guard($guard)->check()) {
                
                if ($guard === 'user') {
                    return redirect()->route('/panel/dashboardadmin'); // Admin aktif -> ke dashboard admin
                }
                
                return redirect()->route('dashboard'); // Siswa aktif -> ke dashboard siswa
            }
        }

        return $next($request);
    }
}