<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h3 class="card-title mb-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2 text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /></svg>
                Rekap Presensi
            </h3>
            <small class="text-muted">{{ date('d M Y', strtotime($tglMulai)) }} s/d {{ date('d M Y', strtotime($tglSelesai)) }} &bull; {{ $hariKerja }} hari kerja &bull; {{ $rekap->count() }} siswa</small>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                Cetak
            </button>
            <a href="{{ route('laporan.export') }}?tgl_mulai={{ $tglMulai }}&tgl_selesai={{ $tglSelesai }}&kelas={{ request('kelas') }}&kode_jurusan={{ request('kode_jurusan') }}"
               class="btn btn-sm btn-success">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M8 11h8v7h-8z" /><path d="M8 15h8" /><path d="M11 11v7" /></svg>
                Export CSV
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    @php
        $totalHadir     = $rekap->sum('total_hadir');
        $totalTerlambat = $rekap->sum('total_terlambat');
        $avgPct = $rekap->count() > 0 && $hariKerja > 0
            ? round($rekap->avg(fn($r) => $hariKerja > 0 ? ($r->total_hadir / $hariKerja) * 100 : 0), 1)
            : 0;
        $siswaRajin = $rekap->sortByDesc('total_hadir')->first();
    @endphp
    <div class="card-body border-bottom py-3">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="text-center">
                    <div class="fs-2 fw-bold text-success">{{ $totalHadir }}</div>
                    <div class="text-muted small">Total Kehadiran</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-center">
                    <div class="fs-2 fw-bold text-danger">{{ $totalTerlambat }}</div>
                    <div class="text-muted small">Total Keterlambatan</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-center">
                    <div class="fs-2 fw-bold text-primary">{{ $avgPct }}%</div>
                    <div class="text-muted small">Rata-rata Kehadiran</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-center">
                    <div class="fs-5 fw-bold text-warning" style="line-height:1.3;">
                        {{ $siswaRajin ? $siswaRajin->nama_lengkap : '-' }}
                    </div>
                    <div class="text-muted small">Siswa Paling Rajin 🏆</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="table-responsive">
        <table class="table table-hover table-vcenter mb-0" id="tabelRekap">
            <thead class="table-dark">
                <tr>
                    <th class="text-center" style="width:45px;">No</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Jurusan</th>
                    <th class="text-center">Hadir</th>
                    <th class="text-center">Terlambat</th>
                    <th class="text-center">% Kehadiran</th>
                    <th style="min-width:140px;">Progress</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekap as $i => $row)
                @php
                    $pct = $hariKerja > 0 ? round(($row->total_hadir / $hariKerja) * 100, 1) : 0;
                    $pct = min(100, $pct);
                    if ($pct >= 90) { $statusLabel = 'Sangat Baik'; $statusColor = 'success'; $barColor = '#2fb344'; }
                    elseif ($pct >= 75) { $statusLabel = 'Baik'; $statusColor = 'primary'; $barColor = '#4299e1'; }
                    elseif ($pct >= 60) { $statusLabel = 'Cukup'; $statusColor = 'warning'; $barColor = '#f76707'; }
                    else { $statusLabel = 'Kurang'; $statusColor = 'danger'; $barColor = '#d63939'; }
                @endphp
                <tr>
                    <td class="text-center text-muted">{{ $i + 1 }}</td>
                    <td>
                        <div class="fw-semibold">{{ $row->nama_lengkap }}</div>
                        <div class="text-muted small">{{ $row->nisn }}</div>
                    </td>
                    <td>{{ $row->kelas }}</td>
                    <td><span class="badge bg-blue-lt text-blue">{{ $row->nama_jurusan }}</span></td>
                    <td class="text-center fw-bold text-success">{{ $row->total_hadir }}</td>
                    <td class="text-center fw-bold {{ $row->total_terlambat > 0 ? 'text-danger' : 'text-muted' }}">
                        {{ $row->total_terlambat }}
                    </td>
                    <td class="text-center fw-bold" style="color:{{ $barColor }};">{{ $pct }}%</td>
                    <td>
                        <div style="height:8px;background:#e9ecef;border-radius:4px;overflow:hidden;">
                            <div style="height:100%;width:{{ $pct }}%;background:{{ $barColor }};border-radius:4px;transition:width .8s;"></div>
                        </div>
                        <div class="text-muted" style="font-size:.7rem;margin-top:2px;">{{ $row->total_hadir }}/{{ $hariKerja }} hari</div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ $statusColor }}-lt text-{{ $statusColor }}">{{ $statusLabel }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /></svg>
                        <p class="mt-2">Tidak ada data untuk filter ini</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
