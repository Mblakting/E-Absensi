<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard siswa — statistik presensi bulan ini.
     */
    public function index()
    {
        $hariini  = now()->toDateString();
        $bulanini = (int) now()->format('m');
        $tahunini = (int) now()->format('Y');

        $nisn      = Auth::guard('siswa')->id();
        $siswaData = DB::table('siswa')->where('nisn', $nisn)->first();

        $hasFaceRegistered = !empty($siswaData->face_descriptor);

        $fotoUrl = !empty($siswaData->foto)
            ? asset('storage/uploads/foto_siswa/' . $siswaData->foto)
            : asset('assets/img/sample/avatar/avatar1.jpg');

        // Presensi hari ini
        $presensihariini = DB::table('presensi')
            ->where('nisn', $nisn)
            ->where('tgl_presensi', $hariini)
            ->first();

        // Histori presensi bulan ini
        $historibulanini = DB::table('presensi')
            ->where('nisn', $nisn)
            ->whereMonth('tgl_presensi', $bulanini)
            ->whereYear('tgl_presensi', $tahunini)
            ->orderBy('tgl_presensi', 'desc')
            ->get();

        // Rekap presensi bulanan
        $rekappresensi = DB::table('presensi')
            ->selectRaw('COUNT(nisn) as jmlhadir, SUM(IF(jam_in > "07:00:00", 1, 0)) as jmlterlambat')
            ->where('nisn', $nisn)
            ->whereMonth('tgl_presensi', $bulanini)
            ->whereYear('tgl_presensi', $tahunini)
            ->first() ?? (object) ['jmlhadir' => 0, 'jmlterlambat' => 0];

        // Leaderboard hari ini (top 20 tercepat masuk)
        $leaderboard = DB::table('presensi')
            ->select('presensi.*', 'siswa.nama_lengkap', 'siswa.kelas', 'siswa.foto')
            ->join('siswa', 'presensi.nisn', '=', 'siswa.nisn')
            ->where('tgl_presensi', $hariini)
            ->orderBy('jam_in')
            ->limit(20)
            ->get()
            ->map(function ($p) {
                $p->foto_url = !empty($p->foto)
                    ? asset('storage/uploads/foto_siswa/' . $p->foto)
                    : asset('assets/img/kosong.jpg');
                return $p;
            });

        // Rekap izin/sakit bulan ini (yang disetujui)
        $rekapizin = DB::table('pengajuan_izin')
            ->selectRaw('SUM(IF(status = "i", 1, 0)) as jmlizin, SUM(IF(status = "s", 1, 0)) as jmlsakit')
            ->where('nisn', $nisn)
            ->whereMonth('tgl_izin', $bulanini)
            ->whereYear('tgl_izin', $tahunini)
            ->where('status_approved', 1)
            ->first() ?? (object) ['jmlizin' => 0, 'jmlsakit' => 0];

        $namabulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',    4 => 'April',
            5 => 'Mei',     6 => 'Juni',     7 => 'Juli',      8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return view('dashboard.dashboard', compact(
            'presensihariini', 'historibulanini', 'namabulan', 'bulanini', 'tahunini',
            'rekappresensi', 'leaderboard', 'rekapizin',
            'fotoUrl', 'hasFaceRegistered'
        ));
    }

    /**
     * Dashboard admin — ringkasan presensi hari ini.
     */
    public function dashboardadmin()
    {
        $hariini = now()->toDateString();

        $rekappresensi = DB::table('presensi')
            ->selectRaw('COUNT(nisn) as jmlhadir, SUM(IF(jam_in > "07:00:00", 1, 0)) as jmlterlambat')
            ->where('tgl_presensi', $hariini)
            ->first() ?? (object) ['jmlhadir' => 0, 'jmlterlambat' => 0];

        $rekapizin = DB::table('pengajuan_izin')
            ->selectRaw('SUM(IF(status = "i", 1, 0)) as jmlizin, SUM(IF(status = "s", 1, 0)) as jmlsakit')
            ->where('tgl_izin', $hariini)
            ->where('status_approved', 1)
            ->first() ?? (object) ['jmlizin' => 0, 'jmlsakit' => 0];

        return view('dashboard.dashboardadmin', compact('rekappresensi', 'rekapizin'));
    }
}
