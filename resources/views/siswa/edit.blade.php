<form action="/siswa/{{ $siswa->nisn ?? '0' }}/update" method="POST" id="frmEditSiswa">
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
        <div class="col-12">
            <div class="form-group">
                <label for="kelas">Kelas</label>
                <input type="text" name="kelas" id="kelas" class="form-control" value="{{ $siswa->kelas }}" required>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </div>
</form>
