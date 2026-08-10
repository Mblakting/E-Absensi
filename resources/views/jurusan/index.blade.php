@extends('layouts.admin.app')
@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Data Jurusan
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
                                <a href="#" class="btn btn-primary" id="btnTambahJurusan">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>    
                                Tambah Data</a>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12"> 
                                <form action="/jurusan" method="GET">
                                    <div class="row">
                                        <div class="col-10">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="nama_jurusan" id="nama_jurusan" placeholder="Cari Jurusan" value="{{ request('nama_jurusan') }}">
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-primary">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
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
                                                <th>No</th> 
                                                <th>Kode Jurusan</th>
                                                <th>Nama Jurusan</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($jurusan as $j)
                                            <tr>
                                                <td>{{$loop->iteration}}</td>
                                                <td>{{$j->kode_jurusan}}</td>
                                                <td>{{$j->nama_jurusan}}</td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-outline-primary edit" kode_jurusan="{{ $j->kode_jurusan }}" title="Edit Jurusan">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                                        Edit
                                                    </a>
                                                    <form action="/jurusan/{{$j->kode_jurusan}}/delete" method="POST" style="display:inline;">
                                                        @csrf
                                                        <a href="#" class="btn btn-sm btn-outline-danger delete-confirm" title="Hapus Jurusan">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                                            Hapus
                                                        </a>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

<div class="modal modal-blur fade" id="modal-inputjurusan" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Tambah Data Jurusan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form action="/jurusan/store" method="POST" id="frmJurusan">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Kode Jurusan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="kode_jurusan" id="kode_jurusan_input" placeholder="Contoh: TKJ">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Jurusan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_jurusan" id="nama_jurusan_input" placeholder="Contoh: Teknik Komputer dan Jaringan">
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
<div class="modal modal-blur fade" id="modal-editjurusan" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Data Jurusan</h5>
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
        $("#btnTambahJurusan").click(function(){
            $("#modal-inputjurusan").modal("show");
        });

        $(".edit").click(function(e){
            e.preventDefault();
            var kode_jurusan = $(this).attr('kode_jurusan');
            $.ajax({
                type:'POST',
                url:'/jurusan/edit',
                cache:false,
                data:{
                    _token: "{{ csrf_token() }}",
                    kode_jurusan: kode_jurusan
                },
                success:function(respond){
                    $("#loadeditform").html(respond);
                    $("#modal-editjurusan").modal("show");
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
                title: "Hapus Data Jurusan?",
                text: "Data jurusan ini akan dihapus permanen!",
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

        $("#frmJurusan").submit(function(){
            var kode_jurusan = $("#kode_jurusan_input").val();
            var nama_jurusan = $("#nama_jurusan_input").val();

            if(kode_jurusan==""){
                Swal.fire({ title:'Warning!', text:'Kode Jurusan harus diisi', icon:'warning', confirmButtonText:'OK' })
                    .then(()=>{ $("#kode_jurusan_input").focus(); });
                return false;
            }
            if(nama_jurusan==""){
                Swal.fire({ title:'Warning!', text:'Nama Jurusan harus diisi', icon:'warning', confirmButtonText:'OK' })
                    .then(()=>{ $("#nama_jurusan_input").focus(); });
                return false;
            }
        })
    });
</script>
@endpush
