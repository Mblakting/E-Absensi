<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import facade Auth
use Illuminate\Support\Facades\DB;  

class DashboardController extends Controller
{
    public function index()
    {
        // Pengecekan otentikasi
        if (!Auth::guard('siswa')->check()) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $hariini = date('Y-m-d');
        $bulanini = (int) date('m');
        $tahunini = date('Y');
        
        // Menggunakan facade Auth yang sudah diimport
        $nisn = Auth::guard('siswa')->user()->nisn; 

        // 1. Presensi Hari Ini
        $presensihariini = DB::table('presensi')
            ->where('nisn', $nisn)
            ->where('tgl_presensi', $hariini)
            ->first();

        // 2. Histori Presensi Bulan Ini (History untuk chart/list)
        $historibulanini = DB::table('presensi')
            ->where('nisn', $nisn)
            ->whereRaw('MONTH(tgl_presensi) = ?', [$bulanini])
            ->whereRaw('YEAR(tgl_presensi) = ?', [$tahunini])
            ->orderBy('tgl_presensi', 'DESC')
            ->get();
            
        // 3. Rekap Presensi Bulanan (Hadir/Terlambat/Izin)
        $rekappresensi = DB::table('presensi')
            ->selectRaw('COUNT(nisn) as jmlhadir, SUM(IF(jam_in > "07:00:00", 1, 0)) as jmlterlambat')
            ->where('nisn', $nisn)
            ->whereRaw('MONTH(tgl_presensi) = ?', [$bulanini]) 
            ->whereRaw('YEAR(tgl_presensi) = ?', [$tahunini])
            ->first();

        if (!$rekappresensi) {
            $rekappresensi = (object)['jmlhadir' => 0, 'jmlterlambat' => 0];
        }

        // 4. Leaderboard Hari Ini
        $leaderboard = DB::table('presensi')
            ->select('presensi.*', 'siswa.nama_lengkap', 'siswa.kelas', 'siswa.foto')
            ->join('siswa', 'presensi.nisn', '=', 'siswa.nisn') 
            ->where('tgl_presensi', $hariini)
            ->orderBy('jam_in')
            ->get();
            
        $namabulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $rekapizin = DB::table('pengajuan_izin')
            ->selectRaw('SUM(IF(status = "i", 1, 0)) as jmlizin, SUM(IF(status = "s", 1, 0)) as jmlsakit')
            ->where('nisn', $nisn)
            ->whereRaw('MONTH(tgl_izin) = ?', [$bulanini])
            ->whereRaw('YEAR(tgl_izin) = ?', [$tahunini])
            ->where('status_approved', 1)
            ->first();

        if (!$rekapizin) {
            $rekapizin = (object)['jmlizin' => 0, 'jmlsakit' => 0];
        }
        
        // Memastikan semua variabel yang diperlukan (termasuk $leaderboard) di compact
        return view('dashboard.dashboard', compact('presensihariini', 'historibulanini', 'namabulan', 'bulanini', 'tahunini', 'rekappresensi', 'leaderboard', 'rekapizin'));
    }

    public function dashboardadmin()
    {
        $hariini = date('Y-m-d');
        $rekappresensi = DB::table('presensi')
            ->selectRaw('COUNT(nisn) as jmlhadir, SUM(IF(jam_in > "07:00:00", 1, 0)) as jmlterlambat')
            ->where('tgl_presensi', $hariini)
            ->first();

        // Pastikan tidak null
        if (!$rekappresensi) {
            $rekappresensi = (object)['jmlhadir' => 0, 'jmlterlambat' => 0];
        }

        $rekapizin = DB::table('pengajuan_izin')
            ->selectRaw('SUM(IF(status = "i", 1, 0)) as jmlizin, SUM(IF(status = "s", 1, 0)) as jmlsakit')
            ->where('tgl_izin', $hariini)
            ->where('status_approved', 1)
            ->first();

        // Pastikan tidak null
        if (!$rekapizin) {
            $rekapizin = (object)['jmlizin' => 0, 'jmlsakit' => 0];
        }

        return view('dashboard.dashboardadmin', compact('rekappresensi', 'rekapizin'));
    }
}