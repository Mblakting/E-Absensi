<form action="/siswa/{{ $siswa->nisn ?? '0' }}/update" method="POST" id="frmEditSiswa" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="nisn_old" value="{{ $siswa->nisn ?? '' }}">

    <div class="row">
        <div class="col-12">
            <div class="form-group">
                <label for="nisn">NISN</label>
                <input type="text" name="nisn" id="nisn" class="form-control" readonly value="{{ $siswa->nisn }}" required>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" value="{{ $siswa->nama_lengkap }}" required>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-6">
            <div class="form-group">
                <label for="kelas">Kelas</label>
                <input type="text" name="kelas" id="kelas" class="form-control" value="{{ $siswa->kelas }}">
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                <label for="no_hp">No HP</label>
                <input type="text" name="no_hp" id="no_hp" class="form-control" value="{{ $siswa->no_hp }}">
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <div class="form-group">
                <label for="kode_jurusan">Jurusan</label>
                <select name="kode_jurusan" id="kode_jurusan" class="form-select">
                    <option value="">Pilih Jurusan</option>
                    @foreach($jurusan as $j)
                        <option 
                            value="{{ $j->kode_jurusan }}" 
                            {{ ($siswa->kode_jurusan ?? '') == $j->kode_jurusan ? 'selected' : '' }}>
                                {{ $j->nama_jurusan }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <div class="form-group">
                <label for="foto">Foto (opsional)</label>
                <input type="file" name="foto" id="foto" class="form-control">
                <input type="hidden" name="old_foto" value="{{$siswa->foto}}">
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </div>
</form>
