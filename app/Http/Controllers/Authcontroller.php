<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\redirect;


class Authcontroller extends Controller
{
// Kasus Login Gagal
public function proseslogin(Request $request)
{
            if (Auth::guard('siswa')->attempt(['nisn' => $request->nisn, 'password' => $request->password])) {
            return redirect('/dashboard');
        }
    // ...
    else {
        // Harus redirect ke rute GET: '/'
        return redirect('/')->with(['warning'=>'NISN atau Password salah']); 
    }
}

// Kasus Logout
public function proseslogout()
{
    if (Auth::guard('siswa')->check()){
        Auth::guard('siswa')->logout();
        // Harus redirect ke rute GET: '/'
        return redirect('/'); 
    }
    return redirect('/'); 
}

    public function prosesloginadmin(Request $request)
    {
        if (Auth::guard('user')->attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect('/panel/dashboardadmin');
        }
        else {
            // Menggunakan fungsi route() untuk rute bernama 'loginadmin' lebih aman
            return redirect()->route('loginadmin')->with(['warning'=>'Email atau Password salah']); 
        }
    }
    
    public function proseslogoutadmin()
    {
        if (Auth::guard('user')->check()){
            Auth::guard('user')->logout();
            // Menggunakan fungsi route() untuk rute bernama 'loginadmin' lebih aman
            return redirect()->route('loginadmin'); 
        }
        // Tambahkan fallback jika proses logout admin dipanggil tanpa otentikasi
        return redirect()->route('loginadmin');
    }

}