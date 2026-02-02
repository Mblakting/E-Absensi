@extends('layouts.admin.tabler')
@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Data Siswa
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                @if(Session::has('success')) 
                                <div class="alert alert-success">
                                    {{ Session::get('success') }}
                                </div>
                                @endif
                                @if(Session::has('warning'))
                                <div class="alert alert-warning">
                                    {{ Session::get('warning') }}
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <a href="#" class="btn btn-primary" id="btnTambahsiswa">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>    
                                Tambah Data</a>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12"> 
                                <form action="/siswa" method="GET">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="nama_siswa" id="nama_siswa" placeholder="Cari Nama Siswa" value="{{ request('nama_siswa') }}">
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                <select name="kode_jurusan" id="kode_jurusan" class="form-select">
                                                    <option value="">Pilih Jurusan</option>
                                                        @foreach($jurusan as $j)
                                                            <option value="{{$j->kode_jurusan}}" 
                                                                {{ request()->get('kode_jurusan') == $j->kode_jurusan ? 'selected' : '' }}>
                                                                {{$j->nama_jurusan}}
                                                            </option>
                                                        @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-primary">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-search"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                                                    Cari
                                                </button>
                                            </div>    
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="table table-bordered">
                                    <table class="table table-hover table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>Foto</th> 
                                                <th>NISN</th>
                                                <th>Nama Siswa</th>
                                                <th>Kelas</th>
                                                <th>No HP</th>
                                                <th>Jurusan</th>
                                                <th>Aksi</th>
                                            </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $rows = isset($siswa) ? $siswa : (isset($siswas) ? $siswas : []);
                                    @endphp
                                    @forelse($rows as $item)
                                        <tr>
                                            <td>
                                                @php
                                                    $fotoPath = public_path('storage/uploads/foto_siswa/' . ($item->foto ?? ''));
                                                @endphp
                                                @if (!empty($item->foto) && file_exists($fotoPath))
                                                    {{-- Tampilkan Foto Siswa jika file benar-benar ada --}}
                                                    <img src="{{ asset('storage/uploads/foto_siswa/' . $item->foto) }}" 
                                                        alt="{{ $item->nama_lengkap }}" 
                                                        width="50" 
                                                        class="avatar avatar-sm"> 
                                                @else
                                                    {{-- Tampilkan Foto Default/Placeholder --}}
                                                    <img src="{{ asset('assets/img/kosong.jpg') }}" 
                                                        alt="No Photo" 
                                                        width="50" 
                                                        class="avatar avatar-sm"> 
                                                @endif
                                            </td>
                                            <td>{{ $item->nisn ?? '-' }}</td>
                                            <td>{{ $item->nama_lengkap ?? '-' }}</td>
                                            <td>{{ $item->kelas ?? '-' }}</td>
                                            <td>{{ $item->no_hp ?? '-' }}</td>
                                            <td>{{ $item->nama_jurusan ?? '-' }}</td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-outline-primary edit" nisn="{{ $item->nisn }}">
                                                    Edit  
                                                </a>
                                                <form action="/siswa/{{$item->nisn}}/delete" method="POST" id="deleteForm{{$item->nisn}}">
                                                    @csrf
                                                    {{-- Tambahkan attribut data-form agar JavaScript tahu form mana yang harus disubmit --}}
                                                    <a href="#" class="btn btn-sm btn-outline-danger mt-2 delete-confirm" 
                                                    data-form-id="deleteForm{{$item->nisn}}"> 
                                                        Hapus
                                                    </a>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Tidak ada data siswa.</td> </tr>
                                    @endforelse
                                        </tbody>
                                    </table>
                                    @if(method_exists($rows, 'links'))
                                        <nav class="d-flex justify-content-center mt-3" aria-label="Page navigation">
                                            {{-- Gunakan template pagination Bootstrap agar sesuai dengan Tabler (Bootstrap-like) --}}
                                            {!! $rows->links('pagination::bootstrap-4') !!}
                                        </nav>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

<div class="modal modal-blur fade" id="modal-inputsiswa" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Tambah Data Siswa</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="/siswa/store" method="POST" id="frmSiswa" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-12">
                        <div class="input-icon mb-3">
                                <span class="input-icon-addon">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-file-barcode"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M8 13h1v3h-1z" /><path d="M12 13v3" /><path d="M15 13h1v3h-1z" /></svg>
                                </span>
                                <input type="text" value="" class="form-control" name="nisn" id="nisn" placeholder="NISN">
                              </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="input-icon mb-3">
                                <span class="input-icon-addon">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-user"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 2a5 5 0 1 1 -5 5l.005 -.217a5 5 0 0 1 4.995 -4.783z" /><path d="M14 14a5 5 0 0 1 5 5v1a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-1a5 5 0 0 1 5 -5h4z" /></svg>
                                </span>
                                <input type="text" value="" class="form-control" name="nama_lengkap" id="nama_lengkap" placeholder="Nama Lengkap Siswa">
                              </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="input-icon mb-3">
                                <span class="input-icon-addon">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-chalkboard"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 19h-3a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v11a1 1 0 0 1 -1 1" /><path d="M11 16m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v1a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" /></svg>
                                </span>
                                <input type="text" value="" class="form-control" name="kelas" id="kelas" placeholder="Kelas">
                              </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="input-icon mb-3">
                                <span class="input-icon-addon">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-phone"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" /></svg>
                                </span>
                                <input type="text" value="" class="form-control" name="no_hp" id="no_hp" placeholder="Nomor HP">
                              </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <input type="file" name="foto" class="form-control">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <select name="kode_jurusan" id="kode_jurusan" class="form-select">
                            <option value="">Pilih Jurusan</option>
                                @foreach($jurusan as $j)
                                    <option value="{{$j->kode_jurusan}}" 
                                        {{ request()->get('kode_jurusan') == $j->kode_jurusan ? 'selected' : '' }}>
                                            {{$j->nama_jurusan}}
                                    </option>
                                @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <div class="form-group">
                            <button class="btn btn-primary w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-send"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 14l11 -11" /><path d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5" /></svg>    
                            Simpan</button>
                        </div>
                    </div>
                </div>
            </form> 
          </div>
        </div>
      </div>
</div>

{{--Modal Edit--}}
<div class="modal modal-blur fade" id="modal-editsiswa" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Data Siswa</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="loadeditform">
            
          </div>
        </div>
      </div>
</div>
@endsection

@push('myscript')
<script>
    $(function(){
        $("#btnTambahsiswa").click(function(){
            $("#modal-inputsiswa").modal("show");
        });

        $(".edit").click(function(e){
            e.preventDefault();
            var nisn = $(this).attr('nisn');
            $.ajax({
                type:'POST',
                url:'/siswa/edit',
                cache:false,
                data:{
                    _token: "{{ csrf_token() }}",
                    nisn: nisn
                },
                success:function(respond){
                    $("#loadeditform").html(respond);
                    $("#modal-editsiswa").modal("show");
                },
                error: function(xhr){
                    console.error('Edit load error:', xhr);
                    alert('Gagal memuat form edit. Cek console untuk detail.');
                }
            });
        });

        $(".delete-confirm").click(function(e){
            var form = $(this).closest('form');
            e.preventDefault
            Swal.fire({
                title: "Menghapus Data?",
                text: "Anda yakin ingin menghapus data siswa ini?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal",
                dangerMode: true,
                confirmButtonColor: '#d33', 
                cancelButtonColor: '#3085d6',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire("Dibatalkan", "Data siswa tidak jadi dihapus.", "info");
                }
            });
        });

        $("#frmSiswa").submit(function(){
            var nisn = $("#nisn").val();
            var nama_lengkap = $("#nama_lengkap").val();
            var kelas = $("#kelas").val();
            var no_hp = $("#no_hp").val();
            var kode_jurusan = $("frmSiswa").find("#kode_jurusan").val();

            if(nisn==""){
                Swal.fire({
                title: 'Warning!',
                text: 'NISN harus diisi',
                icon: 'warning',
                confirmButtonText: 'OK'
                }).then((result)=>{
                    $("#nisn").focus();
                })
                return false;
            }
            else if(nama_lengkap==""){
                Swal.fire({
                title: 'Warning!',
                text: 'Nama harus diisi',
                icon: 'warning',
                confirmButtonText: 'OK'
                }).then((result)=>{
                    $("#nama_lengkap").focus();
                })
                return false;
            }
            else if(kelas==""){
                Swal.fire({
                title: 'Warning!',
                text: 'Kelas harus diisi',
                icon: 'warning',
                confirmButtonText: 'OK'
                }).then((result)=>{
                    $("#kelas").focus();
                })
                return false;
            }
            else if(no_hp==""){
                Swal.fire({
                title: 'Warning!',
                text: 'Nomor HP harus diisi',
                icon: 'warning',
                confirmButtonText: 'OK'
                }).then((result)=>{
                    $("#no_hp").focus();
                })
                return false;
            }
            else if(kode_jurusan==""){
                Swal.fire({
                title: 'Warning!',
                text: 'Jurusan harus diisi',
                icon: 'warning',
                confirmButtonText: 'OK'
                }).then((result)=>{
                    $("#kode_jurusan").focus();
                })
                return false;
            }

        })
    });
</script>
@endpush