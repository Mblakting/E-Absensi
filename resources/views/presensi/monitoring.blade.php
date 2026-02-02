@extends('layouts.admin.tabler')
@section('content')

    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Monitoring Presensi
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
                            <div class="row mb-2 mt-2">
                                <div class="col-12">
                                    <div class="form-group">
                                        <input type="text" class="form-control" value="{{date("Y-m-d")}}" id="tanggal" name="tanggal" placeholder="Tanggal Presensi" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>NISN</th>
                                                <th>Nama Siswa</th>
                                                <th>Jurusan</th>
                                                <th>Jam Masuk</th>
                                                <th>Foto</th>
                                                <th>Jam Pulang</th>
                                                <th>Foto</th>
                                                <th>Keterangan</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="loadpresensi">

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
    <div class="modal modal-blur fade" id="modal-tampilkanpeta" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Lokasi Presensi User</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="loadmap">
            
          </div>
        </div>
      </div>
</div>
@endsection

@push('myscript')
<script>
    // Inisialisasi DatePicker
    $(function() {
        $("#tanggal").datepicker({
            autoclose: true,
            todayHighlight: true,
            format: 'yyyy-mm-dd'
        });
        
        // Panggil fungsi loadpresensi saat halaman dimuat
        loadpresensi();
    });
    
    function loadpresensi() {
        // PERBAIKAN: Ambil nilai tanggal secara eksplisit dari input
        var tanggal = $("#tanggal").val(); 
        
        // Pastikan ada tanggal sebelum mengirim request
        if (tanggal) { 
            $.ajax({
                type: 'POST',
                url: '/presensi/getpresensi', 
                data: {
                    _token: "{{ csrf_token() }}", 
                    tanggal: tanggal
                },
                cache: false,
                success: function(respond) {
                    // Isi tbody dengan data yang diterima dari server
                    $("#loadpresensi").html(respond);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Request Failed:", status, error, xhr.responseText);
                    alert("Gagal memuat data presensi. Cek console untuk detail.");
                }
            });
        }
    }
    
    // Event handler saat tanggal berubah
    $("#tanggal").change(function(e) {
        loadpresensi();
    });
    
</script>
@endpush