@extends('layouts.admin.app')
@section('content')

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2 text-primary" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h5.697" /><path d="M18 14v4h4" /><path d="M18 11v-4a2 2 0 0 0 -2 -2h-2" /><path d="M10 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /></svg>
                    Laporan & Rekap Presensi
                </h2>
                <p class="text-muted mt-1">Analisis kehadiran siswa dengan grafik dan rekap detail</p>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- TREN 7 HARI --}}
        <div class="row row-cards mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2 text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 3v18h18" /><path d="M20 18v3" /><path d="M16 16v5" /><path d="M12 13v8" /><path d="M8 16v5" /><path d="M3 11c3.333 -3.333 6 -5 8 -5c2 0 4 1.5 6 4.5" /></svg>
                            Tren Kehadiran 7 Hari Terakhir
                        </h3>
                        <span class="badge bg-blue-lt" id="tren-status">Memuat...</span>
                    </div>
                    <div class="card-body">
                        <div id="chart-tren" style="min-height:260px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTER REKAP --}}
        <div class="row row-cards mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2 text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v7l-6 2v-8.5l-4.48 -4.928a2 2 0 0 1 -.52 -1.345v-2.227z" /></svg>
                            Filter Rekap Per Siswa
                        </h3>
                    </div>
                    <div class="card-body">
                        <form id="formRekap">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Tanggal Mulai</label>
                                    <input type="text" class="form-control" id="tgl_mulai" name="tgl_mulai"
                                           value="{{ now()->startOfMonth()->format('Y-m-d') }}" autocomplete="off" readonly style="cursor:pointer;">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Tanggal Selesai</label>
                                    <input type="text" class="form-control" id="tgl_selesai" name="tgl_selesai"
                                           value="{{ now()->format('Y-m-d') }}" autocomplete="off" readonly style="cursor:pointer;">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Kelas</label>
                                    <select name="kelas" id="kelas" class="form-select">
                                        <option value="">Semua Kelas</option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k }}">{{ $k }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Jurusan</label>
                                    <select name="kode_jurusan" id="kode_jurusan" class="form-select">
                                        <option value="">Semua Jurusan</option>
                                        @foreach($jurusan as $j)
                                            <option value="{{ $j->kode_jurusan }}">{{ $j->nama_jurusan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end gap-2">
                                    <button type="button" class="btn btn-primary flex-fill" id="btnTampilkan">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                                        Tampilkan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- HASIL REKAP --}}
        <div id="hasilRekap"></div>

    </div>
</div>

@push('myscript')
<script>
// ── DatePicker ──
$(function(){
    $("#tgl_mulai, #tgl_selesai").datepicker({ autoclose:true, todayHighlight:true, format:'yyyy-mm-dd' });

    // Load tren chart
    loadTren();

    // Tombol tampilkan
    $("#btnTampilkan").click(function(){ loadRekap(); });
});

// ── Tren Chart ──
function loadTren() {
    fetch("{{ route('laporan.tren') }}")
        .then(r => r.json())
        .then(data => {
            document.getElementById('tren-status').textContent = '7 hari terakhir';
            document.getElementById('tren-status').className = 'badge bg-green-lt';

            var labels   = data.map(d => d.label);
            var hadir    = data.map(d => d.hadir);
            var terlambat = data.map(d => d.terlambat);

            var options = {
                series: [
                    { name: 'Hadir', data: hadir },
                    { name: 'Terlambat', data: terlambat }
                ],
                chart: { type: 'area', height: 260, toolbar: { show: false }, zoom: { enabled: false } },
                colors: ['#2fb344', '#d63939'],
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] }
                },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: { categories: labels },
                yaxis: { min: 0, labels: { formatter: v => Math.round(v) } },
                tooltip: { shared: true, intersect: false },
                legend: { position: 'top' },
                dataLabels: { enabled: true, style: { fontSize: '11px' } },
                grid: { borderColor: '#f0f0f0' }
            };

            var chart = new ApexCharts(document.querySelector("#chart-tren"), options);
            chart.render();
        })
        .catch(() => {
            document.getElementById('tren-status').textContent = 'Gagal memuat';
            document.getElementById('tren-status').className = 'badge bg-red-lt';
        });
}

// ── Rekap Per Siswa ──
function loadRekap() {
    var btn = document.getElementById('btnTampilkan');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memuat...';

    var data = {
        _token:       "{{ csrf_token() }}",
        tgl_mulai:    document.getElementById('tgl_mulai').value,
        tgl_selesai:  document.getElementById('tgl_selesai').value,
        kelas:        document.getElementById('kelas').value,
        kode_jurusan: document.getElementById('kode_jurusan').value,
    };

    $.ajax({
        type: 'POST',
        url: "{{ route('laporan.rekap') }}",
        data: data,
        cache: false,
        success: function(html) {
            document.getElementById('hasilRekap').innerHTML = html;
            btn.disabled = false;
            btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg> Tampilkan';
        },
        error: function() {
            document.getElementById('hasilRekap').innerHTML = '<div class="alert alert-danger">Gagal memuat data rekap.</div>';
            btn.disabled = false;
            btn.innerHTML = 'Tampilkan';
        }
    });
}
</script>
@endpush
@endsection
