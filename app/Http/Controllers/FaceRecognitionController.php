<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Traits\HasversineDistance;

class FaceRecognitionController extends Controller
{
    use HasversineDistance;
    /**
     * Simpan face descriptor untuk siswa tertentu (admin only).
     */
    public function saveDescriptor(Request $request)
    {
        $request->validate([
            'nisn'       => 'required|string|exists:siswa,nisn',
            'descriptor' => 'required|array|size:128',
        ]);

        $nisn       = $request->nisn;
        $descriptor = $request->descriptor;
        $fotoBase64 = $request->input('foto_snapshot');

        // Pastikan semua elemen descriptor adalah angka
        foreach ($descriptor as $val) {
            if (!is_numeric($val)) {
                return response()->json(['status' => 'error', 'message' => 'Format descriptor wajah tidak valid.'], 422);
            }
        }

        $siswa = DB::table('siswa')->where('nisn', $nisn)->first();

        try {
            $updateData = ['face_descriptor' => json_encode($descriptor), 'updated_at' => now()];

            if (!empty($fotoBase64)) {
                $parts = explode(';base64,', $fotoBase64);
                if (count($parts) === 2) {
                    $imageData = base64_decode($parts[1]);
                    if ($imageData !== false) {
                        $fileName = $nisn . '_face.jpg';
                        $filePath = 'uploads/foto_siswa/' . $fileName;
                        Storage::disk('public')->makeDirectory('uploads/foto_siswa');

                        if (Storage::disk('public')->put($filePath, $imageData)) {
                            $updateData['foto'] = $fileName;
                        }
                    }
                }
            }

            DB::table('siswa')->where('nisn', $nisn)->update($updateData);

            return response()->json([
                'status'  => 'success',
                'message' => 'Data wajah ' . $siswa->nama_lengkap . ' berhasil disimpan!',
            ]);
        } catch (\Exception $e) {
            Log::error('FaceRecognition saveDescriptor error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan data wajah.'], 500);
        }
    }

    /**
     * Hapus face descriptor siswa (admin only).
     */
    public function deleteDescriptor(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string|exists:siswa,nisn',
        ]);

        try {
            DB::table('siswa')->where('nisn', $request->nisn)->update([
                'face_descriptor' => null,
                'updated_at'      => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Data wajah berhasil dihapus.']);
        } catch (\Exception $e) {
            Log::error('FaceRecognition deleteDescriptor error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus data wajah.'], 500);
        }
    }

    /**
     * Ambil semua descriptor wajah yang sudah terdaftar (untuk client-side matching).
     */
    public function getDescriptors()
    {
        $siswas = DB::table('siswa')
            ->select('nisn', 'nama_lengkap', 'kelas', 'foto', 'face_descriptor')
            ->whereNotNull('face_descriptor')
            ->get();

        $data = $siswas->map(function ($s) {
            return [
                'nisn'            => $s->nisn,
                'nama_lengkap'    => $s->nama_lengkap,
                'kelas'           => $s->kelas,
                'foto'            => !empty($s->foto)
                    ? asset('storage/uploads/foto_siswa/' . $s->foto)
                    : null,
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
            return redirect()->route('siswa.index')->with('warning', 'Siswa tidak ditemukan.');
        }

        return view('face.enroll', compact('siswa'));
    }

    /**
     * Halaman absensi dengan face recognition (siswa).
     */
    public function absenFacePage()
    {
        $nisn    = Auth::guard('siswa')->id();
        $hariini = now()->toDateString();

        $cek   = DB::table('presensi')
            ->where('nisn', $nisn)
            ->where('tgl_presensi', $hariini)
            ->first();

        $siswa   = DB::table('siswa')->where('nisn', $nisn)->first();
        $hasFace = !empty($siswa->face_descriptor);

        $fotoSiswa = !empty($siswa->foto)
            ? asset('storage/uploads/foto_siswa/' . $siswa->foto)
            : asset('assets/img/sample/avatar/avatar1.jpg');

        return view('face.absen', compact('cek', 'hasFace', 'fotoSiswa'));
    }

    /**
     * Proses absensi dengan face recognition.
     */
    public function storeAbsenFace(Request $request)
    {
        $request->validate([
            'nisn_match' => 'required|string',
            'confidence' => 'required|numeric',
            'lokasi'     => 'required|string',
            'image'      => 'required|string',
        ]);

        $nisnMatch  = $request->nisn_match;
        $confidence = (float) $request->confidence;
        $lokasi     = $request->lokasi;
        $image      = $request->image;

        // Threshold face-api.js: nilai < 0.55 dianggap cocok
        if ($confidence > 0.55) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Wajah tidak dikenali! Pastikan pencahayaan cukup dan posisi wajah tepat.',
                'type'    => 'face_mismatch',
            ]);
        }

        // Pastikan siswa yang login sama dengan yang dikenali
        $nisnLogin = Auth::guard('siswa')->id();
        if ($nisnMatch !== $nisnLogin) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Wajah tidak cocok dengan akun yang sedang login!',
                'type'    => 'face_mismatch',
            ]);
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

        $tglPresensi = now()->toDateString();
        $jam         = now()->format('H:i:s');

        $cek = DB::table('presensi')
            ->where('nisn', $nisnLogin)
            ->where('tgl_presensi', $tglPresensi)
            ->first();

        if ($cek && !empty($cek->jam_out)) {
            return response()->json([
                'status'  => 'gagal',
                'message' => 'Anda sudah melakukan presensi masuk dan pulang hari ini.',
            ], 409);
        }

        $ket = $cek ? 'out' : 'in';

        // Simpan foto absensi
        $imageParts = explode(';base64,', $image);
        if (count($imageParts) !== 2) {
            return response()->json(['status' => 'gagal', 'message' => 'Format gambar tidak valid.'], 400);
        }

        $imageData = base64_decode($imageParts[1]);
        if ($imageData === false) {
            return response()->json(['status' => 'gagal', 'message' => 'Gagal decode gambar.'], 400);
        }

        $fileName = $nisnLogin . '-' . $tglPresensi . '-' . $ket . '-face.jpg';
        $filePath = 'uploads/absensi/' . $fileName;

        Storage::disk('public')->makeDirectory('uploads/absensi');

        if (!Storage::disk('public')->put($filePath, $imageData)) {
            Log::error("Gagal simpan foto absensi face: {$filePath}");
            return response()->json(['status' => 'gagal', 'message' => 'Gagal menyimpan foto.'], 500);
        }

        if ($cek) {
            DB::table('presensi')
                ->where('nisn', $nisnLogin)
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

        DB::table('presensi')->insert([
            'nisn'         => $nisnLogin,
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

}
