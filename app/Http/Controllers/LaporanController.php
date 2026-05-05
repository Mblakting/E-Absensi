<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Halaman laporan rekap presensi (admin).
     */
    public function index()
    {
        $jurusan = DB::table('jurusan')->orderBy('nama_jurusan')->get();
        $kelas   = DB::table('siswa')->select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

        return view('laporan.index', compact('jurusan', 'kelas'));
    }

    /**
     * Data tren kehadiran 7 hari terakhir (JSON untuk chart).
     */
    public function trenHarian()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $tgl   = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->locale('id')->isoFormat('ddd D/M');

            $hadir = DB::table('presensi')
                ->where('tgl_presensi', $tgl)
                ->count();

            $terlambat = DB::table('presensi')
                ->where('tgl_presensi', $tgl)
                ->where('jam_in', '>', '07:00:00')
                ->count();

            $data[] = compact('tgl', 'label', 'hadir', 'terlambat');
        }

        return response()->json($data);
    }

    /**
     * Rekap presensi per siswa dalam rentang tanggal tertentu.
     */
    public function rekapSiswa(Request $request)
    {
        [$rekap, $hariKerja, $tglMulai, $tglSelesai] = $this->buildRekap($request);

        return view('laporan.rekap', compact('rekap', 'hariKerja', 'tglMulai', 'tglSelesai'));
    }

    /**
     * Export rekap ke CSV (UTF-8 BOM agar Excel bisa baca).
     */
    public function exportCsv(Request $request)
    {
        [$rekap, $hariKerja, $tglMulai, $tglSelesai] = $this->buildRekap($request);

        $filename = 'rekap_presensi_' . $tglMulai . '_sd_' . $tglSelesai . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($rekap, $hariKerja) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM untuk Excel

            fputcsv($file, ['No', 'NISN', 'Nama Lengkap', 'Kelas', 'Jurusan', 'Hadir', 'Terlambat', 'Hari Kerja', '% Kehadiran']);

            foreach ($rekap as $i => $row) {
                $pct = $hariKerja > 0 ? round(($row->total_hadir / $hariKerja) * 100, 1) : 0;
                fputcsv($file, [
                    $i + 1,
                    $row->nisn,
                    $row->nama_lengkap,
                    $row->kelas,
                    $row->nama_jurusan,
                    $row->total_hadir,
                    $row->total_terlambat,
                    $hariKerja,
                    $pct . '%',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    /**
     * Build query rekap presensi — dipakai oleh rekapSiswa() dan exportCsv().
     *
     * @return array [rekap, hariKerja, tglMulai, tglSelesai]
     */
    private function buildRekap(Request $request): array
    {
        $tglMulai    = $request->input('tgl_mulai',   now()->startOfMonth()->format('Y-m-d'));
        $tglSelesai  = $request->input('tgl_selesai', now()->format('Y-m-d'));
        $kelas       = $request->input('kelas');
        $kodeJurusan = $request->input('kode_jurusan');

        $query = DB::table('siswa')
            ->select(
                'siswa.nisn',
                'siswa.nama_lengkap',
                'siswa.kelas',
                'jurusan.nama_jurusan',
                DB::raw('COUNT(presensi.id) as total_hadir'),
                DB::raw('SUM(CASE WHEN presensi.jam_in > "07:00:00" THEN 1 ELSE 0 END) as total_terlambat'),
                DB::raw('SUM(CASE WHEN presensi.jam_out IS NOT NULL THEN 1 ELSE 0 END) as total_pulang')
            )
            ->join('jurusan', 'siswa.kode_jurusan', '=', 'jurusan.kode_jurusan')
            ->leftJoin('presensi', function ($join) use ($tglMulai, $tglSelesai) {
                $join->on('siswa.nisn', '=', 'presensi.nisn')
                     ->whereBetween('presensi.tgl_presensi', [$tglMulai, $tglSelesai]);
            })
            ->groupBy('siswa.nisn', 'siswa.nama_lengkap', 'siswa.kelas', 'jurusan.nama_jurusan')
            ->orderBy('siswa.kelas')
            ->orderBy('siswa.nama_lengkap');

        if ($kelas)       $query->where('siswa.kelas', $kelas);
        if ($kodeJurusan) $query->where('siswa.kode_jurusan', $kodeJurusan);

        $rekap = $query->get();

        // Hitung hari kerja (Senin–Jumat) dalam rentang
        $hariKerja = 0;
        $start = Carbon::parse($tglMulai);
        $end   = Carbon::parse($tglSelesai);
        while ($start->lte($end)) {
            if ($start->isWeekday()) $hariKerja++;
            $start->addDay();
        }

        return [$rekap, $hariKerja, $tglMulai, $tglSelesai];
    }
}
