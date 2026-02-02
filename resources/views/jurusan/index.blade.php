@extends('layouts.admin.tabler')
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
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>    
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
                                                    <a href="#" class="btn btn-sm btn-outline-primary edit" kode_jurusan="{{ $j->kode_jurusan }}">
                                                    Edit  
                                                    </a>
                                                    <form action="/jurusan/{{$j->kode_jurusan}}/delete" method="POST">
                                                        @csrf
                                                        <a href="#" class="btn btn-sm btn-outline-danger mt-2 delete-confirm"> 
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
                <div class="row">
                    <div class="col-12">
                        <div class="input-icon mb-3">
                                <span class="input-icon-addon">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-file-barcode"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M8 13h1v3h-1z" /><path d="M12 13v3" /><path d="M15 13h1v3h-1z" /></svg>
                                </span>
                                <input type="text" value="" class="form-control" name="kode_jurusan" id="kode_jurusan" placeholder="Kode Jurusan">
                              </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="input-icon mb-3">
                                <span class="input-icon-addon">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-user"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 2a5 5 0 1 1 -5 5l.005 -.217a5 5 0 0 1 4.995 -4.783z" /><path d="M14 14a5 5 0 0 1 5 5v1a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-1a5 5 0 0 1 5 -5h4z" /></svg>
                                </span>
                                <input type="text" value="" class="form-control" name="nama_jurusan" id="nama_jurusan" placeholder="Nama Jurusan">
                              </div>
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
            e.preventDefault
            Swal.fire({
                title: "Menghapus Data?",
                text: "Anda yakin ingin menghapus data ini?",
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
                    Swal.fire("Dibatalkan", "Data tidak jadi dihapus.", "info");
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