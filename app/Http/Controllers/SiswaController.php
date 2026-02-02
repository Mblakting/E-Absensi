<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\siswa;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // Import Log untuk debugging

class SiswaController extends Controller
{
    /**
     * Menampilkan daftar siswa dengan fitur pencarian dan paginasi.
     */
    public function index(Request $request)
    {
        $query=siswa::query();
        $query->select('siswa.*','nama_jurusan');
        $query->join('jurusan', 'siswa.kode_jurusan', '=', 'jurusan.kode_jurusan');
        $query->orderBy('nama_lengkap');

        // Filter berdasarkan nama siswa
        if(!empty($request->nama_siswa)){
            $query->where('nama_lengkap','like','%'.$request->nama_siswa.'%');
        }

        // Filter berdasarkan kode jurusan
        if(!empty($request->kode_jurusan)){
        $query->where('siswa.kode_jurusan',$request->kode_jurusan);
        }

        $siswa=$query->paginate(10);

        $jurusan = DB::table('jurusan')->get();
            
        return view('siswa.index', compact('siswa','jurusan')); 
    }

    /**
     * Menyimpan data siswa baru.
     */
public function store(Request $request){
        $nisn = $request->nisn;
        $nama_lengkap = $request->nama_lengkap;
        $kelas = $request->kelas;
        $no_hp = $request->no_hp;
        $kode_jurusan=$request->kode_jurusan;
        $password= Hash::make('12345');
        
        $foto = ""; 
        $disk_path = "uploads/foto_siswa"; // Path dalam folder public (storage/app/public/uploads/foto_siswa)

        // Tentukan nama file foto jika diupload
        if ($request->hasFile('foto')) {
            $foto = $nisn.".".$request->file('foto')->getClientOriginalExtension();
        }

        try{
            $data = [
                'nisn' => $nisn,
                'nama_lengkap' => $nama_lengkap,
                'kelas' => $kelas,
                'no_hp' => $no_hp,
                'kode_jurusan' => $kode_jurusan,
                'foto' => $foto,
                'password' => $password
            ];
            
            $simpan=DB::table('siswa')->insert($data);
            
            if($simpan){
                // Simpan file foto ke disk 'public'
                if($request->hasFile('foto')){
                    // Simpan file ke storage/app/public/uploads/foto_siswa
                    $request->file('foto')->storeAs($disk_path, $foto, 'public');
                }

                return Redirect::back()->with(['success'=>'Data Tersimpan!']);
            }
        } catch (\Exception $e){
            Log::error("Error saat menyimpan siswa: " . $e->getMessage());
            return Redirect::back()->with(['warning'=>'Gagal menyimpan data!']);
        }
    }

    /**
     * Menampilkan form edit siswa via AJAX.
     * Diharapkan menerima parameter POST 'nisn' dan mengembalikan HTML partial.
     */
    public function edit(Request $request) 
    {
        $nisn = $request->nisn;
        
        // 1. Ambil data Siswa berdasarkan NISN
        $siswa = DB::table('siswa')->where('nisn', $nisn)->first();

        // Cek jika siswa tidak ditemukan (seperti yang sudah Anda buat)
        if (!$siswa) {
            return response('Data siswa tidak ditemukan', 404); 
        }

        // 2. Wajib: Ambil data Jurusan
        $jurusan = DB::table('jurusan')->get(); // Mengambil semua data jurusan

        // 3. Kirim kedua variabel ke view
        return view('siswa.edit', compact('siswa', 'jurusan'));
    }

    /**
     * Memperbarui data siswa yang sudah ada.
     */
    public function update(Request $request, $nisn_param){
        // Use route parameter to locate the record (safer if nisn in form is readonly or changed)
        $nisn = $request->nisn;
        $nama_lengkap = $request->nama_lengkap;
        $kelas = $request->kelas;
        $no_hp = $request->no_hp;
        $kode_jurusan = $request->kode_jurusan;
        $password = Hash::make('12345');

        // Find the siswa by the route parameter (original record)
        $siswa = DB::table('siswa')->where('nisn', $nisn_param)->first();

        if (!$siswa) {
            return Redirect::back()->with(['warning' => 'Data siswa tidak ditemukan untuk diupdate.']);
        }

        $old_foto_name = $siswa->foto;
        $disk_path = 'uploads/foto_siswa'; // relative to the 'public' disk

        // Compute new foto name only if a new file is uploaded
        $foto = $old_foto_name;
        if ($request->hasFile('foto')) {
            $extension = $request->file('foto')->getClientOriginalExtension();
            $foto = $nisn . '.' . $extension;
        }

        try {
            // Prepare data to update
            $data = [
                'nama_lengkap' => $nama_lengkap,
                'kelas' => $kelas,
                'no_hp' => $no_hp,
                'kode_jurusan' => $kode_jurusan,
                'foto' => $foto,
                'password' => $password
            ];

            // Update using the original record identifier
            $update = DB::table('siswa')->where('nisn', $nisn_param)->update($data);

            // If a new file was uploaded, handle file replacement
            if ($request->hasFile('foto')) {
                // Delete old photo only when it exists and is present in the public disk
                if (!empty($old_foto_name)) {
                    $old_path = $disk_path . '/' . $old_foto_name;
                    if (Storage::disk('public')->exists($old_path)) {
                        Storage::disk('public')->delete($old_path);
                    }
                }

                // Store the new photo into storage/app/public/uploads/foto_siswa
                $request->file('foto')->storeAs($disk_path, $foto, 'public');
            }

            if ($update || $request->hasFile('foto')) {
                return Redirect::back()->with(['success' => 'Data Diupdate!']);
            }

            return Redirect::back()->with(['warning' => 'Tidak ada perubahan data yang disimpan.']);
        } catch (\Exception $e) {
            Log::error('Error saat mengupdate siswa: ' . $e->getMessage());
            return Redirect::back()->with(['warning' => 'Gagal mengupdate data!']);
        }
    }
    
    /**
     * Menghapus data siswa
     */
     public function delete($nisn)
     {
        $delete=DB::table('siswa')->where('nisn',$nisn)->delete();
        if($delete){
            return Redirect::back()->with(['success'=>'Data Dihapus']);
        }else{
            return Redirect::back()->with(['warning'=>'Data gagal dihapus']); 
        }
     }
}