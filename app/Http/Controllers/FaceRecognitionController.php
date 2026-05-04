<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FaceRecognitionController extends Controller
{
    /**
     * Simpan face descriptor untuk siswa tertentu (admin only).
     * Menerima array descriptor 128-dimensi dari face-api.js
     */
    public function saveDescriptor(Request $request)
    {
        $nisn = $request->input('nisn');
        $descriptor = $request->input('descriptor'); // array float 128 dimensi

        if (!$nisn || !$descriptor) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak lengkap'], 422);
        }

        // Validasi descriptor adalah array dengan 128 elemen
        if (!is_array($descriptor) || count($descriptor) !== 128) {
            return response()->json(['status' => 'error', 'message' => 'Format descriptor wajah tidak valid'], 422);
        }

        $siswa = DB::table('siswa')->where('nisn', $nisn)->first();
        if (!$siswa) {
            return response()->json(['status' => 'error', 'message' => 'Siswa tidak ditemukan'], 404);
        }

        try {
            DB::table('siswa')->where('nisn', $nisn)->update([
                'face_descriptor' => json_encode($descriptor),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Data wajah ' . $siswa->nama_lengkap . ' berhasil disimpan!',
            ]);
        } catch (\Exception $e) {
            Log::error('FaceRecognition saveDescriptor error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan data wajah'], 500);
        }
    }

    /**
     * Hapus face descriptor siswa (admin only).
     */
    public function deleteDescriptor(Request $request)
    {
        $nisn = $request->input('nisn');

        if (!$nisn) {
            return response()->json(['status' => 'error', 'message' => 'NISN tidak ditemukan'], 422);
        }

        try {
            DB::table('siswa')->where('nisn', $nisn)->update(['face_descriptor' => null]);
            return response()->json(['status' => 'success', 'message' => 'Data wajah berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus data wajah'], 500);
        }
    }

    /**
     * Ambil semua descriptor wajah yang sudah terdaftar (untuk client-side matching).
     * Hanya mengembalikan data yang sudah punya face_descriptor.
     */
    public function getDescriptors()
    {
        $siswas = DB::table('siswa')
            ->select('nisn', 'nama_lengkap', 'kelas', 'foto', 'face_descriptor')
            ->whereNotNull('face_descriptor')
            ->get();

        $data = $siswas->map(function ($s) {
            return [
                'nisn'           => $s->nisn,
                'nama_lengkap'   => $s->nama_lengkap,
                'kelas'          => $s->kelas,
                'foto'           => $s->foto ? asset('storage/uploads/foto_siswa/' . $s->foto) : null,
                'face_descriptor' => json_decode($s->face_descriptor, true),
            ];
        });

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    /**
     * Halaman pendaftaran wajah siswa (admin).
     */
    public function enrollPage($nisn)
    {
        $siswa = DB::table('siswa')
            ->select('siswa.*', 'nama_jurusan')
            ->join('jurusan', 'siswa.kode_jurusan', '=', 'jurusan.kode_jurusan')
            ->where('siswa.nisn', $nisn)
            ->first();

        if (!$siswa) {
            return redirect('/siswa')->with('warning', 'Siswa tidak ditemukan');
        }

        return view('face.enroll', compact('siswa'));
    }

    /**
     * Halaman absensi dengan face recognition (siswa).
     * Menggantikan halaman absen biasa.
     */
    public function absenFacePage()
    {
        $nisn = Auth::guard('siswa')->user()->nisn;
        $hariini = date('Y-m-d');

        $cek = DB::table('presensi')
            ->where('nisn', $nisn)
            ->where('tgl_presensi', $hariini)
            ->first();

        // Cek apakah siswa sudah mendaftarkan wajah
        $siswa = DB::table('siswa')->where('nisn', $nisn)->first();
        $hasFace = !empty($siswa->face_descriptor);

        return view('face.absen', compact('cek', 'hasFace'));
    }

    /**
     * Proses absensi dengan face recognition.
     * Menerima: image (base64), lokasi, nisn_match (hasil pencocokan client-side), confidence
     */
    public function storeAbsenFace(Request $request)
    {
        $nisn_match   = $request->input('nisn_match');
        $confidence   = $request->input('confidence'); // jarak euclidean (lebih kecil = lebih mirip)
        $lokasi       = $request->input('lokasi');
        $image        = $request->input('image');

        // Validasi input
        if (!$nisn_match || !$lokasi || !$image) {
            return response()->json(['status' => 'gagal', 'message' => 'Data tidak lengkap'], 422);
        }

        // Threshold: jarak euclidean face-api.js, nilai < 0.6 dianggap cocok
        if ((float)$confidence > 0.55) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Wajah tidak dikenali! Pastikan pencahayaan cukup dan posisi wajah tepat.',
                'type'    => 'face_mismatch'
            ]);
        }

        // Pastikan siswa yang login sama dengan yang dikenali
        $nisn_login = Auth::guard('siswa')->user()->nisn;
        if ($nisn_match !== $nisn_login) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Wajah tidak cocok dengan akun yang login!',
                'type'    => 'face_mismatch'
            ]);
        }

        // Validasi lokasi GPS
        $latitudesekolah  = -7.671325;
        $longitudesekolah = 110.589607;

        $lokasiuser    = explode(',', $lokasi);
        $latitudeuser  = trim($lokasiuser[0]);
        $longitudeuser = trim($lokasiuser[1]);

        $jarak  = $this->distance($latitudesekolah, $longitudesekolah, $latitudeuser, $longitudeuser);
        $radius = round($jarak['meters']);

        if ($radius > 20000) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda berada di luar area sekolah (' . $radius . ' meter)!',
                'type'    => 'out'
            ]);
        }

        $tgl_presensi = date('Y-m-d');
        $jam          = date('H:i:s');

        // Cek apakah sudah absen masuk hari ini
        $cek = DB::table('presensi')
            ->where('nisn', $nisn_login)
            ->where('tgl_presensi', $tgl_presensi)
            ->first();

        $ket = $cek ? 'out' : 'in';

        // Simpan foto absensi
        $image_parts = explode(';base64,', $image);
        if (count($image_parts) !== 2) {
            return response()->json(['status' => 'gagal', 'message' => 'Format gambar tidak valid'], 400);
        }

        $image_base64 = base64_decode($image_parts[1]);
        if ($image_base64 === false) {
            return response()->json(['status' => 'gagal', 'message' => 'Gagal decode gambar'], 400);
        }

        $formatName = $nisn_login . '-' . $tgl_presensi . '-' . $ket . '-face';
        $fileName   = $formatName . '.jpg';
        $filePath   = 'uploads/absensi/' . $fileName;

        Storage::disk('public')->makeDirectory('uploads/absensi');
        $saved = Storage::disk('public')->put($filePath, $image_base64);

        if (!$saved) {
            Log::error("Gagal simpan foto absensi face: $filePath");
            return response()->json(['status' => 'gagal', 'message' => 'Gagal menyimpan foto'], 500);
        }

        // Proses absen masuk / pulang
        if ($cek) {
            if (!empty($cek->jam_out)) {
                return response()->json(['status' => 'gagal', 'message' => 'Anda sudah melakukan presensi masuk dan pulang hari ini'], 409);
            }

            DB::table('presensi')
                ->where('tgl_presensi', $tgl_presensi)
                ->where('nisn', $nisn_login)
                ->update([
                    'jam_out'    => $jam,
                    'foto_out'   => $fileName,
                    'lokasi_out' => $lokasi,
                ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Absen Pulang Berhasil! Hati-Hati Di Jalan 👋',
                'type'    => 'out'
            ]);
        } else {
            DB::table('presensi')->insert([
                'nisn'         => $nisn_login,
                'tgl_presensi' => $tgl_presensi,
                'jam_in'       => $jam,
                'foto_in'      => $fileName,
                'lokasi_in'    => $lokasi,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Absen Masuk Berhasil! Selamat Belajar 🎉',
                'type'    => 'in'
            ]);
        }
    }

    /**
     * Hitung jarak antara dua koordinat GPS (dalam meter).
     */
    private function distance($lat1, $lon1, $lat2, $lon2): array
    {
        $theta     = $lon1 - $lon2;
        $miles     = sin(deg2rad($lat1)) * sin(deg2rad($lat2))
                   + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $miles     = acos($miles);
        $miles     = rad2deg($miles);
        $miles     = $miles * 60 * 1.1515;
        $kilometers = $miles * 1.609344;
        $meters    = $kilometers * 1000;
        return compact('meters');
    }
}
