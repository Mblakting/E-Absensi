@extends('layouts.presensi')

@section('header')
<div class="appHeader" style="background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%); color:#fff; box-shadow: 0 2px 12px rgba(26,115,232,.4);">
    <div class="left" style="padding-left:12px; display:flex; flex-direction:column; justify-content:center;">
        <div style="font-size:0.68rem; opacity:0.8; line-height:1;" id="headerDate"></div>
        <div style="font-size:0.82rem; font-weight:700; line-height:1.3;" id="headerTime"></div>
    </div>
    <div class="pageTitle" style="font-weight:700; letter-spacing:0.3px;">E-Presensi</div>
    <div class="right" style="padding-right:4px;">
        <form action="{{ route('proseslogout') }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin logout?')">
            @csrf
            <button type="submit" class="headerButton" style="color:#fff;background:none;border:none;padding:0;cursor:pointer;">
                <ion-icon name="log-out-outline" style="font-size:22px;"></ion-icon>
            </button>
        </form>
    </div>
</div>

<style>
/* ─── Reset margin-top manual dari halaman lain ─── */
#appCapsule { padding-top: 56px !important; }

/* ─── Hero Card ─── */
.hero-card {
    background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
    border-radius: 20px; color: #fff; padding: 18px 16px;
    position: relative; overflow: hidden;
    box-shadow: 0 6px 24px rgba(26,115,232,.35);
}
.hero-card::before {
    content:''; position:absolute; top:-50px; right:-30px;
    width:150px; height:150px; background:rgba(255,255,255,.07); border-radius:50%;
}
.hero-card::after {
    content:''; position:absolute; bottom:-70px; left:-30px;
    width:200px; height:200px; background:rgba(255,255,255,.04); border-radius:50%;
}
.hero-status-box {
    flex:1; background:rgba(255,255,255,.14); border-radius:12px;
    padding:9px 6px; text-align:center; backdrop-filter:blur(4px);
}
/* ─── Quick Menu ─── */
.qm-wrap { background:#fff; border-radius:16px; padding:14px 8px 10px; text-align:center;
    box-shadow:0 2px 10px rgba(0,0,0,.06); text-decoration:none; color:inherit; display:block; }
.qm-icon { width:50px; height:50px; border-radius:15px; display:flex; align-items:center;
    justify-content:center; margin:0 auto 7px; font-size:22px; }
.qm-label { font-size:.7rem; font-weight:600; color:#444; }
/* ─── Stat Mini ─── */
.stat-mini { background:#fff; border-radius:14px; padding:13px 8px; text-align:center;
    box-shadow:0 2px 10px rgba(0,0,0,.06); }
.stat-num { font-size:1.55rem; font-weight:800; line-height:1; }
.stat-label { font-size:.68rem; color:#6c757d; margin-top:3px; }
/* ─── Progress ─── */
.prog-bar { height:9px; border-radius:5px; background:#e9ecef; overflow:hidden; }
.prog-fill { height:100%; border-radius:5px;
    background:linear-gradient(90deg,#1a73e8,#34a853); transition:width 1.3s cubic-bezier(.4,0,.2,1); }
/* ─── Streak / Motivasi ─── */
.streak-card { background:linear-gradient(135deg,#ff6b35,#f7931e); border-radius:16px;
    padding:16px; color:#fff; box-shadow:0 4px 14px rgba(255,107,53,.3); height:100%; }
.motivasi-card { background:linear-gradient(135deg,#34a853,#1e7e34); border-radius:16px;
    padding:16px; color:#fff; box-shadow:0 4px 14px rgba(52,168,83,.3); height:100%; }
/* ─── Tabs ─── */
.ctabs { display:flex; background:#ebebeb; border-radius:12px; padding:3px; gap:3px; }
.ctab { flex:1; text-align:center; padding:8px 4px; border-radius:10px; font-size:.76rem;
    font-weight:600; cursor:pointer; color:#777; border:none; background:transparent; transition:all .2s; }
.ctab.active { background:#fff; color:#1a73e8; box-shadow:0 2px 8px rgba(0,0,0,.1); }
.tab-pnl { display:none; }
.tab-pnl.active { display:block; }
/* ─── History / Leaderboard items ─── */
.hist-item { background:#fff; border-radius:12px; padding:11px 13px; margin-bottom:7px;
    display:flex; align-items:center; gap:11px; box-shadow:0 1px 6px rgba(0,0,0,.05); }
.lb-item { background:#fff; border-radius:12px; padding:9px 13px; margin-bottom:7px;
    display:flex; align-items:center; gap:11px; box-shadow:0 1px 6px rgba(0,0,0,.05); }
.lb-rank { width:28px; height:28px; border-radius:50%; display:flex; align-items:center;
    justify-content:center; font-size:.73rem; font-weight:700; flex-shrink:0; }
/* ─── Section title ─── */
.sec-title { font-size:.78rem; font-weight:700; color:#555; text-transform:uppercase;
    letter-spacing:.5px; margin-bottom:9px; display:flex; align-items:center; gap:5px; }
</style>
@endsection

@section('content')
@php
    $siswaLogin = Auth::guard('siswa')->user();
    // $fotoUrl dan $hasFaceRegistered sudah dikirim dari DashboardController

    // Streak kehadiran berturut-turut
    $streak = 0;
    $checkDate = \Carbon\Carbon::today();
    for ($i = 0; $i < 60; $i++) {
        $ada = \DB::table('presensi')
            ->where('nisn', $siswaLogin->nisn)
            ->where('tgl_presensi', $checkDate->format('Y-m-d'))
            ->exists();
        if ($ada) { $streak++; $checkDate->subDay(); }
        else break;
    }

    // Persentase kehadiran bulan ini
    $hariKerja = 0;
    $now = \Carbon\Carbon::now();
    for ($d = 1; $d <= $now->day; $d++) {
        $tgl = \Carbon\Carbon::create($now->year, $now->month, $d);
        if ($tgl->isWeekday()) $hariKerja++;
    }
    $pctHadir = $hariKerja > 0 ? min(100, round(($rekappresensi->jmlhadir / $hariKerja) * 100)) : 0;

    $motivasiList = [
        'Semangat belajar hari ini! Setiap langkah kecil membawa perubahan besar. 💪',
        'Kehadiran adalah kunci kesuksesan. Kamu sudah di jalur yang benar! 🌟',
        'Konsistensi mengalahkan bakat. Terus hadir dan terus berkembang! 🚀',
        'Hari ini adalah kesempatan baru untuk menjadi lebih baik. Manfaatkan! ✨',
        'Setiap hari yang kamu hadir adalah investasi untuk masa depanmu. 🎯',
        'Disiplin hari ini = kesuksesan esok hari. Tetap semangat! 🏆',
        'Kamu luar biasa! Kehadiranmu membuat perbedaan. 🌈',
    ];
    $motivasiHari = $motivasiList[(int)date('N') - 1];

    $sudahMasuk  = $presensihariini != null;
    $sudahPulang = $presensihariini != null && !empty($presensihariini->jam_out);
@endphp

{{-- ═══ HERO CARD ═══ --}}
<div style="padding: 14px 12px 0;">
    <div class="hero-card">
        {{-- Profil baris atas --}}
        <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1;">
            <div style="position:relative; flex-shrink:0;">
                <img src="{{ $fotoUrl }}" alt="avatar"
                     style="width:54px;height:54px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,.45);"
                     onerror="this.src='{{ asset('assets/img/sample/avatar/avatar1.jpg') }}'">
                {{-- Badge status online (sudah absen masuk, belum pulang) --}}
                @if($sudahMasuk && !$sudahPulang)
                    <span title="Sedang di sekolah" style="position:absolute;bottom:1px;right:1px;
                          width:14px;height:14px;background:#34a853;border-radius:50%;
                          border:2px solid #fff;"></span>
                @endif
                {{-- Badge wajah terdaftar --}}
                @if($hasFaceRegistered)
                    <span title="Wajah terdaftar" style="position:absolute;top:-2px;right:-2px;
                          width:18px;height:18px;background:#fbbc04;border-radius:50%;
                          border:2px solid #fff;display:flex;align-items:center;
                          justify-content:center;font-size:9px;line-height:1;">🤖</span>
                @endif
            </div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:.7rem;opacity:.8;">Selamat datang,</div>
                <div style="font-size:1rem;font-weight:700;line-height:1.25;
                     white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $siswaLogin->nama_lengkap }}
                </div>
                <div style="font-size:.68rem;opacity:.75;margin-top:1px;">
                    {{ $siswaLogin->kelas }} &bull; NISN: {{ $siswaLogin->nisn }}
                </div>
            </div>
        </div>

        {{-- Status hari ini --}}
        <div style="margin-top:14px; position:relative; z-index:1;">
            <div style="font-size:.65rem;opacity:.75;margin-bottom:7px;
                 text-transform:uppercase;letter-spacing:.5px;">Status Hari Ini</div>
            <div class="d-flex gap-2">
                <div class="hero-status-box">
                    <div style="font-size:1rem;">{{ $sudahMasuk ? '✅' : '⏳' }}</div>
                    <div style="font-size:.6rem;opacity:.8;margin-top:1px;">Masuk</div>
                    <div style="font-size:.78rem;font-weight:700;">
                        {{ $sudahMasuk ? $presensihariini->jam_in : '--:--' }}
                    </div>
                </div>
                <div class="hero-status-box">
                    <div style="font-size:1rem;">{{ $sudahPulang ? '✅' : '⏳' }}</div>
                    <div style="font-size:.6rem;opacity:.8;margin-top:1px;">Pulang</div>
                    <div style="font-size:.78rem;font-weight:700;">
                        {{ $sudahPulang ? $presensihariini->jam_out : '--:--' }}
                    </div>
                </div>
                <div class="hero-status-box">
                    <div style="font-size:1rem;">🔥</div>
                    <div style="font-size:.6rem;opacity:.8;margin-top:1px;">Streak</div>
                    <div style="font-size:.78rem;font-weight:700;">{{ $streak }} Hari</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ QUICK MENU ═══ --}}
<div style="padding: 14px 12px 0;">
    <div class="sec-title"><ion-icon name="grid-outline"></ion-icon> Menu Cepat</div>
    <div class="row g-2">
        <div class="col-3">
            <a href="{{ route('presensi.face') }}" class="qm-wrap">
                <div class="qm-icon" style="background:#e8f0fe;">
                    <ion-icon name="scan" style="color:#1a73e8;"></ion-icon>
                </div>
                <div class="qm-label">Absen</div>
            </a>
        </div>
        <div class="col-3">
            <a href="{{ route('presensi.histori') }}" class="qm-wrap">
                <div class="qm-icon" style="background:#fce8e6;">
                    <ion-icon name="document-text" style="color:#ea4335;"></ion-icon>
                </div>
                <div class="qm-label">Histori</div>
            </a>
        </div>
        <div class="col-3">
            <a href="{{ route('presensi.izin') }}" class="qm-wrap">
                <div class="qm-icon" style="background:#fef7e0;">
                    <ion-icon name="calendar-number" style="color:#f9a825;"></ion-icon>
                </div>
                <div class="qm-label">Izin</div>
            </a>
        </div>
        <div class="col-3">
            <a href="{{ route('presensi.editprofile') }}" class="qm-wrap">
                <div class="qm-icon" style="background:#e6f4ea;">
                    <ion-icon name="person" style="color:#34a853;"></ion-icon>
                </div>
                <div class="qm-label">Profil</div>
            </a>
        </div>
    </div>
</div>

{{-- ═══ REKAP BULAN INI ═══ --}}
<div style="padding: 14px 12px 0;">
    <div class="sec-title"><ion-icon name="bar-chart-outline"></ion-icon>
        Rekap {{ $namabulan[$bulanini] }} {{ $tahunini }}
    </div>
    <div class="row g-2 mb-3">
        <div class="col-3">
            <div class="stat-mini">
                <div class="stat-num" style="color:#1a73e8;">{{ $rekappresensi->jmlhadir ?? 0 }}</div>
                <div class="stat-label">Hadir</div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-mini">
                <div class="stat-num" style="color:#ea4335;">{{ $rekappresensi->jmlterlambat ?? 0 }}</div>
                <div class="stat-label">Telat</div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-mini">
                <div class="stat-num" style="color:#f9a825;">{{ $rekapizin->jmlizin ?? 0 }}</div>
                <div class="stat-label">Izin</div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-mini">
                <div class="stat-num" style="color:#34a853;">{{ $rekapizin->jmlsakit ?? 0 }}</div>
                <div class="stat-label">Sakit</div>
            </div>
        </div>
    </div>

    {{-- Progress kehadiran --}}
    <div style="background:#fff;border-radius:14px;padding:13px 14px;box-shadow:0 2px 10px rgba(0,0,0,.06);">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span style="font-size:.78rem;font-weight:600;color:#333;">Tingkat Kehadiran Bulan Ini</span>
            <span style="font-size:.88rem;font-weight:800;color:#1a73e8;" id="pctText">0%</span>
        </div>
        <div class="prog-bar">
            <div class="prog-fill" id="progressFill" style="width:0%;"></div>
        </div>
        <div style="font-size:.68rem;color:#6c757d;margin-top:5px;">
            {{ $rekappresensi->jmlhadir ?? 0 }} hari hadir dari estimasi {{ $hariKerja }} hari kerja
        </div>
    </div>
</div>

{{-- ═══ STREAK & MOTIVASI ═══ --}}
<div style="padding: 14px 12px 0;">
    <div class="row g-2">
        <div class="col-6">
            <div class="streak-card">
                <div style="font-size:1.6rem;line-height:1;">🔥</div>
                <div style="font-size:1.5rem;font-weight:800;line-height:1;margin-top:4px;">{{ $streak }}</div>
                <div style="font-size:.7rem;opacity:.9;margin-top:3px;">Hari Berturut-turut</div>
                <div style="font-size:.62rem;opacity:.75;margin-top:5px;">
                    @if($streak >= 20) 🏆 Luar Biasa!
                    @elseif($streak >= 10) ⭐ Keren!
                    @elseif($streak >= 5) 👍 Bagus!
                    @else Mulai streak-mu!
                    @endif
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="motivasi-card">
                <div style="font-size:.95rem;">💡</div>
                <div style="font-size:.7rem;line-height:1.45;margin-top:6px;opacity:.95;">
                    {{ $motivasiHari }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ TABS: HISTORI & LEADERBOARD ═══ --}}
<div style="padding: 14px 12px 16px;">
    <div class="ctabs mb-3">
        <button class="ctab active" onclick="switchTab('histori',this)">📋 Absen Bulan Ini</button>
        <button class="ctab" onclick="switchTab('leaderboard',this)">🏆 Leaderboard</button>
    </div>

    {{-- Tab Histori --}}
    <div class="tab-pnl active" id="tab-histori">
        @forelse($historibulanini as $d)
        <div class="hist-item">
            <div style="width:9px;height:9px;border-radius:50%;flex-shrink:0;
                 background:{{ $d->jam_in >= '07:00:00' ? '#ea4335' : '#34a853' }};"></div>
            <div style="flex:1;">
                <div style="font-size:.8rem;font-weight:600;color:#333;">
                    {{ date('d M Y', strtotime($d->tgl_presensi)) }}
                </div>
                <div style="font-size:.7rem;color:#6c757d;margin-top:1px;">
                    Masuk: <strong>{{ $d->jam_in }}</strong>
                    @if($d->jam_out) &nbsp;|&nbsp; Pulang: <strong>{{ $d->jam_out }}</strong> @endif
                </div>
            </div>
            <span style="font-size:.63rem;font-weight:600;border-radius:8px;padding:3px 8px;
                 background:{{ $d->jam_in >= '07:00:00' ? '#fce8e6' : '#e6f4ea' }};
                 color:{{ $d->jam_in >= '07:00:00' ? '#ea4335' : '#34a853' }};">
                {{ $d->jam_in >= '07:00:00' ? 'Terlambat' : 'Tepat Waktu' }}
            </span>
        </div>
        @empty
        <div style="text-align:center;padding:36px 0;color:#aaa;">
            <ion-icon name="calendar-outline" style="font-size:44px;opacity:.3;"></ion-icon>
            <p style="margin-top:8px;font-size:.82rem;">Belum ada data absen bulan ini</p>
        </div>
        @endforelse
    </div>

    {{-- Tab Leaderboard --}}
    <div class="tab-pnl" id="tab-leaderboard">
        @forelse($leaderboard as $d)
        @php
            $lbFoto = !empty($d->foto)
                ? url(Storage::url('uploads/foto_siswa/'.$d->foto))
                : asset('assets/img/sample/avatar/avatar1.jpg');
            $rankColors = ['#FFD700','#C0C0C0','#CD7F32'];
            $rankColor  = $rankColors[$loop->index] ?? '#e9ecef';
            $rankEmoji  = $loop->index < 3 ? ['🥇','🥈','🥉'][$loop->index] : $loop->iteration;
        @endphp
        <div class="lb-item">
            <div class="lb-rank" style="background:{{ $rankColor }};color:{{ $loop->index < 3 ? '#fff' : '#555' }};">
                {{ $rankEmoji }}
            </div>
            <img src="{{ $lbFoto }}" alt="{{ $d->nama_lengkap }}"
                 style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;">
            <div style="flex:1;min-width:0;">
                <div style="font-size:.8rem;font-weight:600;color:#333;
                     white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $d->nama_lengkap }}</div>
                <div style="font-size:.68rem;color:#6c757d;">{{ $d->kelas }}</div>
            </div>
            <span style="font-size:.68rem;font-weight:700;border-radius:8px;padding:4px 9px;flex-shrink:0;
                 background:{{ $d->jam_in >= '07:00:00' ? '#fce8e6' : '#e6f4ea' }};
                 color:{{ $d->jam_in >= '07:00:00' ? '#ea4335' : '#34a853' }};">
                {{ $d->jam_in }}
            </span>
        </div>
        @empty
        <div style="text-align:center;padding:36px 0;color:#aaa;">
            <ion-icon name="trophy-outline" style="font-size:44px;opacity:.3;"></ion-icon>
            <p style="margin-top:8px;font-size:.82rem;">Belum ada data leaderboard hari ini</p>
        </div>
        @endforelse
    </div>
</div>

@push('myscripts')
<script>
// Live clock di header
(function(){
    var days=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    var months=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    function tick(){
        var now=new Date();
        var de=document.getElementById('headerDate');
        var te=document.getElementById('headerTime');
        if(de) de.textContent=days[now.getDay()]+', '+now.getDate()+' '+months[now.getMonth()]+' '+now.getFullYear();
        if(te){
            var h=String(now.getHours()).padStart(2,'0');
            var m=String(now.getMinutes()).padStart(2,'0');
            var s=String(now.getSeconds()).padStart(2,'0');
            te.textContent=h+':'+m+':'+s;
        }
    }
    tick(); setInterval(tick,1000);
})();

// Animasi progress bar
window.addEventListener('load',function(){
    var pct={{ $pctHadir }};
    setTimeout(function(){
        var f=document.getElementById('progressFill');
        var t=document.getElementById('pctText');
        if(f) f.style.width=pct+'%';
        if(t) t.textContent=pct+'%';
    },500);
});

// Tab switcher
function switchTab(name,btn){
    document.querySelectorAll('.tab-pnl').forEach(function(el){el.classList.remove('active');});
    document.querySelectorAll('.ctab').forEach(function(el){el.classList.remove('active');});
    document.getElementById('tab-'+name).classList.add('active');
    btn.classList.add('active');
}
</script>
@endpush
@endsection
