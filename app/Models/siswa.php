<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Siswa extends Authenticatable
{
    use Notifiable;

    protected $table      = 'siswa';
    protected $primaryKey = 'nisn';
    protected $keyType    = 'string';
    public    $incrementing = false;

    protected $fillable = [
        'nisn',
        'nama_lengkap',
        'kelas',
        'no_hp',
        'foto',
        'kode_jurusan',
        'password',
        'face_descriptor',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'face_descriptor',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'kode_jurusan', 'kode_jurusan');
    }

    // NOTE: Presensi and PengajuanIzin models do not exist in this project.
    // The application uses DB::table() queries directly instead of Eloquent models.
    // These relationships are commented out to prevent "Class not found" errors.
    
    // public function presensi()
    // {
    //     return $this->hasMany(Presensi::class, 'nisn', 'nisn');
    // }

    // public function pengajuanIzin()
    // {
    //     return $this->hasMany(PengajuanIzin::class, 'nisn', 'nisn');
    // }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * URL foto profil siswa (fallback ke avatar default).
     */
    public function getFotoUrlAttribute(): string
    {
        return !empty($this->foto)
            ? asset('storage/uploads/foto_siswa/' . $this->foto)
            : asset('assets/img/sample/avatar/avatar1.jpg');
    }

    /**
     * Apakah siswa sudah mendaftarkan wajah.
     */
    public function getHasFaceAttribute(): bool
    {
        return !empty($this->face_descriptor);
    }
}
