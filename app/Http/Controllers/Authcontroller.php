<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authcontroller extends Controller
{
    /**
     * Proses login siswa menggunakan NISN dan password.
     */
    public function proseslogin(Request $request)
    {
        $request->validate([
            'nisn'     => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::guard('siswa')->attempt([
            'nisn'     => $request->nisn,
            'password' => $request->password,
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return redirect()->route('login')
            ->with('warning', 'NISN atau Password salah.')
            ->withInput($request->only('nisn'));
    }

    /**
     * Proses logout siswa.
     */
    public function proseslogout(Request $request)
    {
        Auth::guard('siswa')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * Proses login admin menggunakan email dan password.
     */
    public function prosesloginadmin(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::guard('user')->attempt([
            'email'    => $request->email,
            'password' => $request->password,
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboardadmin'));
        }

        return redirect()->route('loginadmin')
            ->with('warning', 'Email atau Password salah.')
            ->withInput($request->only('email'));
    }

    /**
     * Proses logout admin.
     */
    public function proseslogoutadmin(Request $request)
    {
        Auth::guard('user')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('loginadmin');
    }
}
