<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            if (in_array('user', $this->guards)) {
                return route('loginadmin'); // Admin gagal -> ke /panel
            }
            return route('login'); // Siswa gagal -> ke /
        }
        return null;
    }
}