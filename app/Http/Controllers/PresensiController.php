<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\SchoolSetting;
use App\Traits\HasversineDistance;

class PresensiController extends Controller
{
    use HasversineDistance;

    /**
     * Halaman form absensi siswa.
     */
    public function create()
    {
        $nisn    = Auth::guard('siswa')->id();
        $hariini = now()->toDateString();
        $sekolah = Schema::hasTable('school_settings')
            ? SchoolSetting::query()->first()
            : null;

        $cek = DB::table('presensi')
            ->where('nisn', $nisn)
            ->where('tgl_presensi', $hariini)
            ->count();

        return view('presensi.create', compact('cek', 'sekolah'));
    }

    /**
     * Proses simpan absensi (masuk / pulang) via GPS + foto.
     */
    public function store(Request $request)
    {
        $nisn         = Auth::guard('siswa')->id();
        $tglPresensi  = now()->toDateString();
        $jam          = now()->format('H:i:s');

        $lokasi = $request->input('lokasi');
        $image  = $request->input('image');

        if (!$lokasi || !$image) {
            return response()->json([
                'status'  => 'gagal',
                'message' => 'Data tidak lengkap (lokasi atau gambar hilang).',
            ], 422);
        }

        // Validasi koordinat & radius sekolah
        $lokasiCheck = $this->validateSchoolRadius($lokasi);
        if (!$lokasiCheck['valid']) {
            $statusCode = isset($lokasiCheck['type']) ? 200 : 422;
            return response()->json([
                'status'  => isset($lokasiCheck['type']) ? 'error' : 'gagal',
                'message' => $lokasiCheck['message'],
                'type'    => $lokasiCheck['type'] ?? null,
            ], $statusCode);
        }

        // Cek status absensi hari ini
        $cek = DB::table('presensi')
            ->where('nisn', $nisn)
            ->where('tgl_presensi', $tglPresensi)
            ->first();

        if ($cek && !empty($cek->jam_out)) {
            return response()->json([
                'status'  => 'gagal',
                'message' => 'Anda sudah melakukan presensi masuk dan pulang hari ini.',
            ], 409);
        }

        $ket = $cek ? 'out' : 'in';

        // Proses simpan gambar
        $imageParts = explode(';base64,', $image);
        if (count($imageParts) !== 2) {
            return response()->json(['status' => 'gagal', 'message' => 'Format gambar tidak valid.'], 400);
        }

        $imageData = base64_decode($imageParts[1]);
        if ($imageData === false) {
            return response()->json(['status' => 'gagal', 'message' => 'Gagal decode gambar.'], 400);
        }

        $fileName = $nisn . '-' . $tglPresensi . '-' . $ket . '.jpg';
        $filePath = 'uploads/absensi/' . $fileName;

        Storage::disk('public')->makeDirectory('uploads/absensi');

        if (!Storage::disk('public')->put($filePath, $imageData)) {
            Log::error("Gagal simpan foto absensi: {$filePath}");
            return response()->json(['status' => 'gagal', 'message' => 'Gagal menyimpan foto.'], 500);
        }

        if ($cek) {
            // Absen pulang
            DB::table('presensi')
                ->where('nisn', $nisn)
                ->where('tgl_presensi', $tglPresensi)
                ->update([
                    'jam_out'    => $jam,
                    'foto_out'   => $fileName,
                    'lokasi_out' => $lokasi,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Absen Pulang Berhasil! Hati-Hati Di Jalan 👋',
                'type'    => 'out',
            ]);
        }

        // Absen masuk
        DB::table('presensi')->insert([
            'nisn'         => $nisn,
            'tgl_presensi' => $tglPresensi,
            'jam_in'       => $jam,
            'foto_in'      => $fileName,
            'lokasi_in'    => $lokasi,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Absen Masuk Berhasil! Selamat Belajar 🎉',
            'type'    => 'in',
        ]);
    }

    /**
     * Halaman edit profil siswa.
     */
    public function editprofile()
    {
        $nisn  = Auth::guard('siswa')->id();
        $siswa = DB::table('siswa')->where('nisn', $nisn)->first();

        return view('presensi.editprofile', compact('siswa'));
    }

    /**
     * Proses update profil siswa.
     */
    public function updateprofile(Request $request)
    {
        $nisn  = Auth::guard('siswa')->id();
        $siswa = DB::table('siswa')->where('nisn', $nisn)->first();

        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'kelas'        => 'required|string|max:20',
            'no_hp'        => 'nullable|string|max:20',
            'alamat'       => 'nullable|string|max:500',
            'nama_ortu'    => 'nullable|string|max:100',
            'password'     => 'nullable|string|min:5|confirmed',
            'foto'         => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
        ]);

        $data = [
            'nama_lengkap' => $request->nama_lengkap,
            'kelas'        => $request->kelas,
            'no_hp'        => $request->no_hp,
            'alamat'       => $request->alamat,
            'nama_ortu'    => $request->nama_ortu,
            'updated_at'   => now(),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('foto')) {
            $fotoBaru = $nisn . '.' . $request->file('foto')->getClientOriginalExtension();
            if (!empty($siswa->foto)) {
                Storage::disk('public')->delete('uploads/foto_siswa/' . $siswa->foto);
            }
            $request->file('foto')->storeAs('uploads/foto_siswa', $fotoBaru, 'public');
            $data['foto'] = $fotoBaru;
        }

        DB::table('siswa')->where('nisn', $nisn)->update($data);

        return redirect()->back()->with('success', 'Profil berhasil diupdate.');
    }

    /**
     * Halaman histori presensi siswa.
     */
    public function histori()
    {
        $namabulan = [
            '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        return view('presensi.histori', compact('namabulan'));
    }

    /**
     * Ambil data histori presensi berdasarkan bulan & tahun (AJAX).
     */
    public function gethistori(Request $request)
    {
        $nisn  = Auth::guard('siswa')->id();
        $bulan = (int) $request->input('bulan');
        $tahun = (int) $request->input('tahun');

        $histori = DB::table('presensi')
            ->where('nisn', $nisn)
            ->when($bulan, fn ($q) => $q->whereMonth('tgl_presensi', $bulan))
            ->when($tahun, fn ($q) => $q->whereYear('tgl_presensi', $tahun))
            ->orderBy('tgl_presensi', 'desc')
            ->get();

        return view('presensi.gethistori', compact('histori'));
    }

    /**
     * Halaman daftar pengajuan izin siswa.
     */
    public function izin()
    {
        $dataizin = DB::table('pengajuan_izin')
            ->where('nisn', Auth::guard('siswa')->id())
            ->orderBy('tgl_izin', 'desc')
            ->get();

        return view('presensi.izin', compact('dataizin'));
    }

    /**
     * Halaman form buat izin.
     */
    public function buatizin()
    {
        return view('presensi.buatizin');
    }

    /**
     * Proses simpan pengajuan izin/sakit.
     */
    public function storeizin(Request $request)
    {
        $request->validate([
            'tgl_izin'   => 'required|date',
            'status'     => 'required|in:i,s',
            'keterangan' => 'required|string|max:500',
        ]);

        $nisn    = Auth::guard('siswa')->id();
        $tglIzin = $request->tgl_izin;

        // Cek duplikasi izin pada tanggal yang sama
        $sudahAda = DB::table('pengajuan_izin')
            ->where('nisn', $nisn)
            ->where('tgl_izin', $tglIzin)
            ->exists();

        if ($sudahAda) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda sudah mengajukan izin pada tanggal ' . \Carbon\Carbon::parse($tglIzin)->format('d-m-Y') . '.',
            ], 409);
        }

        try {
            DB::table('pengajuan_izin')->insert([
                'nisn'            => $nisn,
                'tgl_izin'        => $tglIzin,
                'status'          => $request->status,
                'keterangan'      => $request->keterangan,
                'status_approved' => 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            return response()->json([
                'status'   => 'success',
                'message'  => 'Izin berhasil diajukan.',
                'redirect' => route('presensi.izin'),
            ]);
        } catch (\Exception $e) {
            Log::error('Storeizin Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal mengajukan izin.'], 500);
        }
    }

    // ─── Admin Methods ────────────────────────────────────────────────────────

    /**
     * Halaman monitoring presensi (admin).
     */
    public function monitoring()
    {
        return view('presensi.monitoring');
    }

    /**
     * API: jumlah & data presensi terbaru untuk live polling (admin).
     */
    public function monitoringCount(Request $request)
    {
        $tanggal = $request->input('tanggal', now()->toDateString());
        $sinceId = (int) $request->input('since_id', 0);

        $count  = DB::table('presensi')->where('tgl_presensi', $tanggal)->count();
        $lastId = DB::table('presensi')->where('tgl_presensi', $tanggal)->max('id') ?? 0;

        $newEntries = DB::table('presensi')
            ->select('presensi.*', 'siswa.nama_lengkap', 'siswa.kelas', 'siswa.foto')
            ->join('siswa', 'presensi.nisn', '=', 'siswa.nisn')
            ->where('tgl_presensi', $tanggal)
            ->where('presensi.id', '>', $sinceId)
            ->orderBy('presensi.id', 'desc')
            ->get()
            ->map(function ($p) {
                $p->foto_url = !empty($p->foto)
                    ? asset('storage/uploads/foto_siswa/' . $p->foto)
                    : asset('assets/img/kosong.jpg');
                return $p;
            });

        return response()->json([
            'count'       => $count,
            'last_id'     => $lastId,
            'new_entries' => $newEntries,
        ]);
    }

    /**
     * Ambil data presensi berdasarkan tanggal (admin, AJAX).
     */
    public function getPresensi(Request $request)
    {
        $tanggal = $request->input('tanggal', now()->toDateString());

        $presensi = DB::table('presensi')
            ->select('presensi.*', 'nama_lengkap', 'nama_jurusan')
            ->join('siswa', 'presensi.nisn', '=', 'siswa.nisn')
            ->leftJoin('jurusan', 'siswa.kode_jurusan', '=', 'jurusan.kode_jurusan')
            ->where('tgl_presensi', $tanggal)
            ->orderBy('jam_in')
            ->get();

        return view('presensi.getpresensi', compact('presensi'));
    }

    /**
     * Tampilkan peta lokasi absensi (admin).
     */
    public function tampilkanpeta(Request $request)
    {
        $presensi = DB::table('presensi')
            ->join('siswa', 'presensi.nisn', '=', 'siswa.nisn')
            ->where('presensi.id', $request->input('id'))
            ->first();

        if (!$presensi) {
            return redirect()->back()->with('warning', 'Data presensi tidak ditemukan.');
        }

        return view('presensi.showmap', compact('presensi'));
    }

    /**
     * Halaman daftar pengajuan izin/sakit (admin).
     */
    public function izinSakit()
    {
        $izin_sakit = DB::table('pengajuan_izin')
            ->join('siswa', 'pengajuan_izin.nisn', '=', 'siswa.nisn')
            ->select('pengajuan_izin.*', 'siswa.nama_lengkap', 'siswa.kelas')
            ->orderBy('tgl_izin', 'desc')
            ->get();

        return view('presensi.izin_sakit', compact('izin_sakit'));
    }

    /**
     * Approve / tolak pengajuan izin (admin).
     */
    public function approveIzinSakit(Request $request)
    {
        $request->validate([
            'id_izin_sakit_form' => 'required|integer|exists:pengajuan_izin,id',
            'status_approve'     => 'required|in:1,2',
        ]);

        try {
            DB::table('pengajuan_izin')
                ->where('id', $request->id_izin_sakit_form)
                ->update([
                    'status_approved' => $request->status_approve,
                    'updated_at'      => now(),
                ]);

            return redirect()->route('presensi.izin-sakit')->with('success', 'Status izin berhasil diupdate.');
        } catch (\Exception $e) {
            return redirect()->route('presensi.izin-sakit')->with('warning', 'Gagal mengupdate status izin.');
        }
    }

    /**
     * Batalkan / reset status izin ke pending (admin).
     */
    public function batalkanIzinSakit($id)
    {
        try {
            DB::table('pengajuan_izin')
                ->where('id', $id)
                ->update([
                    'status_approved' => 0,
                    'updated_at'      => now(),
                ]);

            return redirect()->route('presensi.izin-sakit')->with('success', 'Status izin berhasil direset ke pending.');
        } catch (\Exception $e) {
            return redirect()->route('presensi.izin-sakit')->with('warning', 'Gagal mereset status izin.');
        }
    }

}
