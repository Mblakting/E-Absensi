@extends('layouts.admin.app')
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
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>    
                                Tambah Data</a>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12"> 
                                <form action="{{ route('siswa.index') }}" method="GET">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="nama_siswa" id="nama_siswa" placeholder="🔍 Cari Nama Siswa" value="{{ request('nama_siswa') }}">
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                <select name="kode_jurusan" id="kode_jurusan" class="form-select">
                                                    <option value="">Semua Jurusan</option>
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
                                                <button type="submit" class="btn btn-primary w-100">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                                                    Cari
                                                </button>
                                            </div>    
                                        </div>
                                        <div class="col-2">
                                            <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary w-100">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                                                Reset
                                            </a>
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
                                                <a href="#" class="btn btn-sm btn-outline-primary edit" nisn="{{ $item->nisn }}" title="Edit Data Siswa">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                                    Edit
                                                </a>
                                                <a href="{{ route('face.enroll', $item->nisn) }}" 
                                                   class="btn btn-sm {{ !empty($item->face_descriptor ?? '') ? 'btn-success' : 'btn-outline-secondary' }}"
                                                   title="{{ !empty($item->face_descriptor ?? '') ? 'Wajah sudah terdaftar - klik untuk update' : 'Daftarkan wajah siswa' }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 8v-2a2 2 0 0 1 2 -2h2" /><path d="M4 16v2a2 2 0 0 0 2 2h2" /><path d="M16 4h2a2 2 0 0 1 2 2v2" /><path d="M16 20h2a2 2 0 0 0 2 -2v-2" /><path d="M9 10a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" /><path d="M13 10a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" /><path d="M9.5 15a3.5 3.5 0 0 0 5 0" /></svg>
                                                    Wajah
                                                </a>
                                                <form action="{{ route('siswa.delete', $item->nisn) }}" method="POST" id="deleteForm{{$item->nisn}}" style="display:inline;">
                                                    @csrf
                                                    <a href="#" class="btn btn-sm btn-outline-danger delete-confirm" 
                                                       data-form-id="deleteForm{{$item->nisn}}" title="Hapus Data Siswa">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
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
            <form action="{{ route('siswa.store') }}" method="POST" id="frmSiswa" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">NISN <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nisn" id="nisn" placeholder="Contoh: 0012345678">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_lengkap" id="nama_lengkap" placeholder="Nama lengkap siswa">
                </div>
                <div class="mb-3">
                    <label class="form-label">Kelas <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="kelas" id="kelas" placeholder="Contoh: XII TKJ 1">
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary w-100">Simpan</button>
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
            e.preventDefault();
            Swal.fire({
                title: "Hapus Data Siswa?",
                text: "Data siswa ini akan dihapus permanen dan tidak bisa dikembalikan!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal",
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
            }).then((result) => {
                if (result.isConfirmed) {
                    form[0].submit();
                }
            });
        });

        $("#frmSiswa").submit(function(){
            var nisn = $("#nisn").val();
            var nama_lengkap = $("#nama_lengkap").val();
            var kelas = $("#kelas").val();

            if(nisn==""){
                Swal.fire({ title:'Warning!', text:'NISN harus diisi', icon:'warning', confirmButtonText:'OK' })
                    .then(()=>{ $("#nisn").focus(); });
                return false;
            }
            if(nama_lengkap==""){
                Swal.fire({ title:'Warning!', text:'Nama harus diisi', icon:'warning', confirmButtonText:'OK' })
                    .then(()=>{ $("#nama_lengkap").focus(); });
                return false;
            }
            if(kelas==""){
                Swal.fire({ title:'Warning!', text:'Kelas harus diisi', icon:'warning', confirmButtonText:'OK' })
                    .then(()=>{ $("#kelas").focus(); });
                return false;
            }

        })
    });
</script>
@endpush
