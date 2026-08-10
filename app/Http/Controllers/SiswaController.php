<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Siswa;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SiswaController extends Controller
{
    /**
     * Menampilkan daftar siswa dengan fitur pencarian dan paginasi.
     */
    public function index(Request $request)
    {
        $query = Siswa::query()
            ->select('siswa.*', 'nama_jurusan')
            ->leftJoin('jurusan', 'siswa.kode_jurusan', '=', 'jurusan.kode_jurusan')
            ->orderBy('nama_lengkap');

        if (!empty($request->nama_siswa)) {
            $query->where('nama_lengkap', 'like', '%' . $request->nama_siswa . '%');
        }

        if (!empty($request->kode_jurusan)) {
            $query->where('siswa.kode_jurusan', $request->kode_jurusan);
        }

        $siswa   = $query->paginate(10)->withQueryString();
        $jurusan = DB::table('jurusan')->orderBy('nama_jurusan')->get();

        return view('siswa.index', compact('siswa', 'jurusan'));
    }

    /**
     * Menyimpan data siswa baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nisn'         => 'required|string|max:20|unique:siswa,nisn',
            'nama_lengkap' => 'required|string|max:100',
            'kelas'        => 'required|string|max:20',
            'kode_jurusan' => 'required|string|exists:jurusan,kode_jurusan',
            'no_hp'        => 'nullable|string|max:20',
            'foto'         => 'nullable|image|max:2048',
        ]);

        try {
            $fotoName = null;
            if ($request->hasFile('foto')) {
                $fotoName = time() . '_' . $request->file('foto')->getClientOriginalName();
                $request->file('foto')->storeAs('uploads/foto_siswa', $fotoName, 'public');
            }

            DB::table('siswa')->insert([
                'nisn'         => $request->nisn,
                'nama_lengkap' => $request->nama_lengkap,
                'kelas'        => $request->kelas,
                'no_hp'        => $request->no_hp,
                'kode_jurusan' => $request->kode_jurusan,
                'foto'         => $fotoName,
                'password'     => Hash::make('12345'),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            return redirect()->back()->with('success', 'Data siswa berhasil disimpan!');
        } catch (\Exception $e) {
            Log::error('Error menyimpan siswa: ' . $e->getMessage());
            return redirect()->back()->with('warning', 'Gagal menyimpan data siswa!');
        }
    }

    /**
     * Menampilkan form edit siswa via AJAX.
     */
    public function edit(Request $request)
    {
        $siswa = DB::table('siswa')->where('nisn', $request->nisn)->first();

        if (!$siswa) {
            return response('Data siswa tidak ditemukan', 404);
        }

        $jurusan = DB::table('jurusan')->orderBy('nama_jurusan')->get();

        return view('siswa.edit', compact('siswa', 'jurusan'));
    }

    /**
     * Memperbarui data siswa yang sudah ada.
     */
    public function update(Request $request, $nisn)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'kelas'        => 'required|string|max:20',
        ]);

        $siswa = DB::table('siswa')->where('nisn', $nisn)->first();

        if (!$siswa) {
            return redirect()->back()->with('warning', 'Data siswa tidak ditemukan.');
        }

        try {
            DB::table('siswa')->where('nisn', $nisn)->update([
                'nama_lengkap' => $request->nama_lengkap,
                'kelas'        => $request->kelas,
                'updated_at'   => now(),
            ]);

            return redirect()->back()->with('success', 'Data siswa berhasil diupdate!');
        } catch (\Exception $e) {
            Log::error('Error mengupdate siswa: ' . $e->getMessage());
            return redirect()->back()->with('warning', 'Gagal mengupdate data siswa!');
        }
    }

    /**
     * Menghapus data siswa beserta foto-nya.
     */
    public function delete($nisn)
    {
        $siswa = DB::table('siswa')->where('nisn', $nisn)->first();

        if (!$siswa) {
            return redirect()->back()->with('warning', 'Data siswa tidak ditemukan.');
        }

        try {
            // Hapus foto profil jika ada
            if (!empty($siswa->foto)) {
                Storage::disk('public')->delete('uploads/foto_siswa/' . $siswa->foto);
            }

            DB::table('siswa')->where('nisn', $nisn)->delete();

            return redirect()->back()->with('success', 'Data siswa berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error menghapus siswa: ' . $e->getMessage());
            return redirect()->back()->with('warning', 'Gagal menghapus data siswa!');
        }
    }
}
