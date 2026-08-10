<form action="/jurusan/{{$jurusan->kode_jurusan}}/update" method="POST">
    @csrf
    <div class="mb-3">
        <label class="form-label">Kode Jurusan</label>
        <input type="text" class="form-control" name="kode_jurusan" value="{{ $jurusan->kode_jurusan }}" readonly>
    </div>
    <div class="mb-3">
        <label class="form-label">Nama Jurusan <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="nama_jurusan" value="{{ $jurusan->nama_jurusan }}" required>
    </div>
    <div class="mt-3">
        <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
    </div>
</form>
