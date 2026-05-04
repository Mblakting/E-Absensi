@extends('layouts.admin.tabler')
@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Overview</div>
                    <h2 class="page-title">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2 text-primary" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /></svg>
                        Dashboard Admin
                    </h2>
                </div>
                <div class="col-auto ms-auto">
                    <div class="text-muted" id="dashboard-date" style="font-size:0.9rem;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">

            {{-- STAT CARDS - Clickable --}}
            <div class="row row-deck row-cards mb-4">
                {{-- Hadir --}}
                <div class="col-sm-6 col-xl-3">
                    <a href="/presensi/monitoring" class="text-decoration-none">
                        <div class="card card-sm stat-card" style="cursor:pointer;transition:transform .18s,box-shadow .18s;">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="bg-success text-white avatar avatar-md rounded-circle shadow">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18.9 7a8 8 0 0 1 1.1 5v1a6 6 0 0 0 .8 3" /><path d="M8 11a4 4 0 0 1 8 0v1a10 10 0 0 0 2 6" /><path d="M12 11v2a14 14 0 0 0 2.5 8" /><path d="M8 15a18 18 0 0 0 1.8 6" /><path d="M4.9 19a22 22 0 0 1 -.9 -7v-1a8 8 0 0 1 12 -6.95" /></svg>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <div class="font-weight-medium fs-3 text-success">{{$rekappresensi->jmlhadir ?? 0}}</div>
                                        <div class="text-muted">Siswa Hadir Hari Ini</div>
                                    </div>
                                    <div class="col-auto text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- Terlambat --}}
                <div class="col-sm-6 col-xl-3">
                    <a href="/presensi/monitoring" class="text-decoration-none">
                        <div class="card card-sm stat-card" style="cursor:pointer;transition:transform .18s,box-shadow .18s;">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="bg-danger text-white avatar avatar-md rounded-circle shadow">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 7v5l3 3" /></svg>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <div class="font-weight-medium fs-3 text-danger">{{$rekappresensi->jmlterlambat ?? 0}}</div>
                                        <div class="text-muted">Siswa Terlambat</div>
                                    </div>
                                    <div class="col-auto text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- Izin --}}
                <div class="col-sm-6 col-xl-3">
                    <a href="/presensi/izin-sakit" class="text-decoration-none">
                        <div class="card card-sm stat-card" style="cursor:pointer;transition:transform .18s,box-shadow .18s;">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="bg-info text-white avatar avatar-md rounded-circle shadow">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M9 12h6" /><path d="M9 16h6" /></svg>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <div class="font-weight-medium fs-3 text-info">{{$rekapizin->jmlizin ?? 0}}</div>
                                        <div class="text-muted">Siswa Izin</div>
                                    </div>
                                    <div class="col-auto text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- Sakit --}}
                <div class="col-sm-6 col-xl-3">
                    <a href="/presensi/izin-sakit" class="text-decoration-none">
                        <div class="card card-sm stat-card" style="cursor:pointer;transition:transform .18s,box-shadow .18s;">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="bg-warning text-white avatar avatar-md rounded-circle shadow">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M10 14l4 0" /><path d="M12 12l0 4" /></svg>
                                        </span>
                                    </div>
                                    <div class="col">
                                        <div class="font-weight-medium fs-3 text-warning">{{$rekapizin->jmlsakit ?? 0}}</div>
                                        <div class="text-muted">Siswa Sakit</div>
                                    </div>
                                    <div class="col-auto text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            {{-- CHART + QUICK ACTIONS --}}
            <div class="row row-cards">
                {{-- Donut Chart --}}
                <div class="col-lg-5">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2 text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                                Rekap Presensi Hari Ini
                            </h3>
                        </div>
                        <div class="card-body d-flex align-items-center justify-content-center">
                            <div id="chart-donut" style="min-height:220px;width:100%;"></div>
                        </div>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="col-lg-7">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2 text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 5h8" /><path d="M13 9h5" /><path d="M13 15h8" /><path d="M13 19h5" /><path d="M3 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" /><path d="M3 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" /></svg>
                                Aksi Cepat
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-6">
                                    <a href="/presensi/monitoring" class="btn btn-outline-success w-100 py-3 quick-action-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-1" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 4m0 1a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-16a1 1 0 0 1 -1 -1z" /><path d="M7 20h10" /><path d="M9 16v4" /><path d="M15 16v4" /></svg>
                                        <div class="fw-bold">Monitoring</div>
                                        <div class="small text-muted">Lihat presensi hari ini</div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="/presensi/izin-sakit" class="btn btn-outline-warning w-100 py-3 quick-action-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-1" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M10 14l4 0" /><path d="M12 12l0 4" /></svg>
                                        <div class="fw-bold">Izin / Sakit</div>
                                        <div class="small text-muted">Proses pengajuan izin</div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="/siswa" class="btn btn-outline-primary w-100 py-3 quick-action-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-1" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                                        <div class="fw-bold">Data Siswa</div>
                                        <div class="small text-muted">Kelola data siswa</div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="/jurusan" class="btn btn-outline-info w-100 py-3 quick-action-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-1" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M9 8l1 0" /><path d="M9 12l1 0" /><path d="M9 16l1 0" /><path d="M14 8l1 0" /><path d="M14 12l1 0" /><path d="M14 16l1 0" /><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" /></svg>
                                        <div class="fw-bold">Data Jurusan</div>
                                        <div class="small text-muted">Kelola jurusan</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important;
        }
        .quick-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: transform .15s, box-shadow .15s;
        }
        .quick-action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
        }
    </style>
@endsection

@push('myscript')
<script>
    // Tanggal di header
    (function(){
        var days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        var months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        var now = new Date();
        var el = document.getElementById('dashboard-date');
        if(el) el.textContent = days[now.getDay()] + ', ' + now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
    })();

    // Donut Chart
    document.addEventListener("DOMContentLoaded", function () {
        var hadir   = {{ $rekappresensi->jmlhadir ?? 0 }};
        var terlambat = {{ $rekappresensi->jmlterlambat ?? 0 }};
        var izin    = {{ $rekapizin->jmlizin ?? 0 }};
        var sakit   = {{ $rekapizin->jmlsakit ?? 0 }};
        var total   = hadir + izin + sakit;

        var options = {
            series: [hadir, terlambat, izin, sakit],
            chart: { type: 'donut', height: 220, toolbar: { show: false } },
            labels: ['Hadir', 'Terlambat', 'Izin', 'Sakit'],
            colors: ['#2fb344', '#d63939', '#4299e1', '#f76707'],
            legend: { position: 'bottom', fontSize: '13px' },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total Siswa',
                                formatter: function(w) { return total; }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            responsive: [{ breakpoint: 480, options: { chart: { height: 200 } } }]
        };

        if (total > 0) {
            var chart = new ApexCharts(document.querySelector("#chart-donut"), options);
            chart.render();
        } else {
            document.querySelector("#chart-donut").innerHTML =
                '<div class="text-center text-muted py-5"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M9 10l.01 0" /><path d="M15 10l.01 0" /><path d="M9.5 15a3.5 3.5 0 0 0 5 0" /></svg><p class="mt-2">Belum ada data presensi hari ini</p></div>';
        }
    });
</script>
@endpush
