<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Jurusan;

class JurusanController extends Controller
{
    /**
     * Menampilkan daftar jurusan dengan fitur pencarian.
     */
    public function index(Request $request)
    {
        $query = Jurusan::query()->orderBy('kode_jurusan');

        if (!empty($request->nama_jurusan)) {
            $query->where('nama_jurusan', 'like', '%' . $request->nama_jurusan . '%');
        }

        $jurusan = $query->get();

        return view('jurusan.index', compact('jurusan'));
    }

    /**
     * Menyimpan data jurusan baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_jurusan' => 'required|string|max:10|unique:jurusan,kode_jurusan',
            'nama_jurusan' => 'required|string|max:100',
        ]);

        try {
            DB::table('jurusan')->insert([
                'kode_jurusan' => strtoupper($request->kode_jurusan),
                'nama_jurusan' => $request->nama_jurusan,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            return redirect()->back()->with('success', 'Data jurusan berhasil disimpan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('warning', 'Gagal menyimpan data jurusan!');
        }
    }

    /**
     * Menampilkan form edit jurusan via AJAX.
     */
    public function edit(Request $request)
    {
        $jurusan = DB::table('jurusan')->where('kode_jurusan', $request->kode_jurusan)->first();

        if (!$jurusan) {
            return response('Data jurusan tidak ditemukan', 404);
        }

        return view('jurusan.edit', compact('jurusan'));
    }

    /**
     * Memperbarui data jurusan.
     */
    public function update($kode_jurusan, Request $request)
    {
        $request->validate([
            'nama_jurusan' => 'required|string|max:100',
        ]);

        try {
            DB::table('jurusan')->where('kode_jurusan', $kode_jurusan)->update([
                'nama_jurusan' => $request->nama_jurusan,
                'updated_at'   => now(),
            ]);

            return redirect()->back()->with('success', 'Data jurusan berhasil diupdate!');
        } catch (\Exception $e) {
            return redirect()->back()->with('warning', 'Gagal mengupdate data jurusan!');
        }
    }

    /**
     * Menghapus data jurusan.
     */
    public function delete($kode_jurusan)
    {
        // Cek apakah jurusan masih digunakan oleh siswa
        $jumlahSiswa = DB::table('siswa')->where('kode_jurusan', $kode_jurusan)->count();

        if ($jumlahSiswa > 0) {
            return redirect()->back()->with('warning', 'Jurusan tidak dapat dihapus karena masih memiliki ' . $jumlahSiswa . ' siswa!');
        }

        try {
            DB::table('jurusan')->where('kode_jurusan', $kode_jurusan)->delete();
            return redirect()->back()->with('success', 'Data jurusan berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('warning', 'Gagal menghapus data jurusan!');
        }
    }
}
