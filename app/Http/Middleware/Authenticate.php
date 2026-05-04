<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            // Cek guards yang sedang digunakan dari route middleware
            $guards = $this->guards ?? [];
            if (in_array('user', $guards)) {
                return route('loginadmin'); // Admin gagal -> ke /panel
            }
            return route('login'); // Siswa gagal -> ke /
        }
        return null;
    }

    protected function authenticate($request, array $guards)
    {
        // Simpan guards agar bisa diakses di redirectTo
        $this->guards = $guards;
        parent::authenticate($request, $guards);
    }
}