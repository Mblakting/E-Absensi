@extends('layouts.admin.tabler')
@section('content')

    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2 text-primary" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 4m0 1a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1z" /><path d="M7 20h10" /><path d="M9 16v4" /><path d="M15 16v4" /></svg>
                        Monitoring Presensi
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <button class="btn btn-outline-secondary me-2" id="btnRefresh" title="Refresh Data">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                        Refresh
                    </button>
                    <button class="btn btn-outline-success" id="btnPrint" title="Cetak Halaman">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                        Cetak
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center w-100 g-2">
                                <div class="col-md-4">
                                    <label class="form-label mb-1 fw-semibold">Pilih Tanggal</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /></svg>
                                        </span>
                                        <input type="text" class="form-control" value="{{date("Y-m-d")}}" id="tanggal" name="tanggal" placeholder="Tanggal Presensi" autocomplete="off" readonly style="cursor:pointer;">
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button class="btn btn-primary w-100" id="btnCariPresensi">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                                        Tampilkan
                                    </button>
                                </div>
                                <div class="col-md-5 d-flex align-items-end justify-content-end">
                                    <span class="badge bg-success-lt me-2 px-3 py-2" id="badge-hadir">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                        Hadir: <strong id="count-hadir">0</strong>
                                    </span>
                                    <span class="badge bg-danger-lt px-3 py-2" id="badge-terlambat">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 7v5l3 3" /></svg>
                                        Terlambat: <strong id="count-terlambat">0</strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-vcenter mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-center" style="width:50px;">No</th>
                                            <th>NISN</th>
                                            <th>Nama Siswa</th>
                                            <th>Jurusan</th>
                                            <th class="text-center">Jam Masuk</th>
                                            <th class="text-center">Foto Masuk</th>
                                            <th class="text-center">Jam Pulang</th>
                                            <th class="text-center">Foto Pulang</th>
                                            <th class="text-center">Keterangan</th>
                                            <th class="text-center">Lokasi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="loadpresensi">
                                        <tr>
                                            <td colspan="10" class="text-center py-5 text-muted">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 4m0 1a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1z" /></svg>
                                                <p class="mt-2">Klik "Tampilkan" untuk memuat data</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Peta --}}
    <div class="modal modal-blur fade" id="modal-tampilkanpeta" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2 text-primary" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 18.5l-3 -1.5l-6 3v-13l6 -3l6 3l6 -3v7.5" /><path d="M9 4v13" /><path d="M15 7v5.5" /><path d="M21.121 20.121a3 3 0 1 0 -4.242 0c.418 .419 1.125 1.045 2.121 1.879c1.051 -.89 1.759 -1.516 2.121 -1.879z" /><path d="M19 18v.01" /></svg>
                Lokasi Presensi Siswa
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-0" id="loadmap" style="min-height:300px;">
          </div>
        </div>
      </div>
    </div>
@endsection

@push('myscript')
<script>
    $(function() {
        // Inisialisasi DatePicker
        $("#tanggal").datepicker({
            autoclose: true,
            todayHighlight: true,
            format: 'yyyy-mm-dd'
        });

        // Auto-load saat halaman dibuka
        loadpresensi();

        // Tombol Tampilkan
        $("#btnCariPresensi").click(function() {
            loadpresensi();
        });

        // Tombol Refresh
        $("#btnRefresh").click(function() {
            loadpresensi();
            var btn = $(this);
            btn.find('svg').css('animation', 'spin 0.6s linear');
            setTimeout(function(){ btn.find('svg').css('animation', ''); }, 700);
        });

        // Tombol Cetak
        $("#btnPrint").click(function() {
            window.print();
        });

        // Trigger saat tanggal berubah
        $("#tanggal").change(function() {
            loadpresensi();
        });
    });

    function loadpresensi() {
        var tanggal = $("#tanggal").val();
        if (!tanggal) return;

        $("#loadpresensi").html('<tr><td colspan="10" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Memuat data...</p></td></tr>');

        $.ajax({
            type: 'POST',
            url: '/presensi/getpresensi',
            data: {
                _token: "{{ csrf_token() }}",
                tanggal: tanggal
            },
            cache: false,
            success: function(respond) {
                $("#loadpresensi").html(respond);

                // Hitung badge
                var hadir = $("#loadpresensi tr").length;
                var terlambat = $("#loadpresensi .badge-terlambat").length;
                $("#count-hadir").text(hadir > 0 ? hadir : 0);
                $("#count-terlambat").text(terlambat);
            },
            error: function(xhr) {
                $("#loadpresensi").html('<tr><td colspan="10" class="text-center text-danger py-4">Gagal memuat data. Silakan coba lagi.</td></tr>');
            }
        });
    }

    // Animasi spin untuk tombol refresh
    var style = document.createElement('style');
    style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
    document.head.appendChild(style);
</script>
@endpush
