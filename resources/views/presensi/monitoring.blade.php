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
            <div class="col-auto ms-auto d-print-none d-flex align-items-center gap-2">
                {{-- Live indicator --}}
                <div class="d-flex align-items-center gap-2 me-2">
                    <span id="liveDot" style="width:10px;height:10px;border-radius:50%;background:#2fb344;display:inline-block;animation:blink 1.5s infinite;"></span>
                    <span class="text-muted small" id="liveLabel">Live</span>
                    <span class="text-muted small" id="nextRefresh"></span>
                </div>
                <button class="btn btn-sm btn-outline-secondary" id="btnToggleLive" title="Pause/Resume auto-refresh">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="iconLive"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 5m0 1a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v12a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1z" /><path d="M14 5m0 1a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v12a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1z" /></svg>
                    Pause
                </button>
                <button class="btn btn-sm btn-outline-success" id="btnPrint">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                    Cetak
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes blink { 0%,100%{opacity:1;} 50%{opacity:.3;} }
@keyframes slideIn { from{transform:translateX(-20px);opacity:0;} to{transform:translateX(0);opacity:1;} }
.new-row { animation: slideIn .4s ease; background: #f0fff4 !important; }
.stat-live { border-radius:12px; padding:16px; text-align:center; transition:all .3s; }
.stat-live .num { font-size:2rem; font-weight:800; line-height:1; }
.stat-live .lbl { font-size:.75rem; color:#6c757d; margin-top:4px; }
.notification-toast {
    position:fixed; top:80px; right:20px; z-index:9999;
    background:#fff; border-radius:12px; padding:12px 16px;
    box-shadow:0 8px 32px rgba(0,0,0,.15); border-left:4px solid #2fb344;
    display:flex; align-items:center; gap:12px; max-width:320px;
    animation:slideIn .3s ease;
}
</style>

<div class="page-body">
    <div class="container-xl">

        {{-- STAT CARDS LIVE --}}
        <div class="row row-cards mb-4">
            <div class="col-6 col-md-3">
                <div class="card stat-live">
                    <div class="num text-success" id="statHadir">0</div>
                    <div class="lbl">✅ Hadir</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-live">
                    <div class="num text-danger" id="statTerlambat">0</div>
                    <div class="lbl">⏰ Terlambat</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-live">
                    <div class="num text-primary" id="statPulang">0</div>
                    <div class="lbl">🏠 Sudah Pulang</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stat-live">
                    <div class="num text-warning" id="statBelumPulang">0</div>
                    <div class="lbl">📍 Belum Pulang</div>
                </div>
            </div>
        </div>

        {{-- FILTER --}}
        <div class="card mb-4">
            <div class="card-body py-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold mb-1">Tanggal</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /></svg>
                            </span>
                            <input type="text" class="form-control" id="tanggal" value="{{ date('Y-m-d') }}" autocomplete="off" readonly style="cursor:pointer;">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100" id="btnCari">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                            Tampilkan
                        </button>
                    </div>
                    <div class="col-md-5 d-flex align-items-center gap-2 justify-content-end">
                        <span class="text-muted small">Terakhir diperbarui: <strong id="lastUpdate">-</strong></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABEL --}}
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-vcenter mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" style="width:50px;">No</th>
                                <th>Siswa</th>
                                <th>Jurusan</th>
                                <th class="text-center">Jam Masuk</th>
                                <th class="text-center">Foto Masuk</th>
                                <th class="text-center">Jam Pulang</th>
                                <th class="text-center">Foto Pulang</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Lokasi</th>
                            </tr>
                        </thead>
                        <tbody id="loadpresensi">
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <div class="spinner-border text-primary mb-2" role="status"></div>
                                    <p>Memuat data...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
                <h5 class="modal-title">Lokasi Presensi Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="loadmap" style="min-height:300px;"></div>
        </div>
    </div>
</div>

{{-- Notification container --}}
<div id="notifContainer"></div>

@push('myscript')
<script>
var lastId = 0;
var isLive = true;
var countdown = 30;
var countdownTimer = null;
var refreshTimer = null;
var currentTanggal = "{{ date('Y-m-d') }}";

$(function(){
    $("#tanggal").datepicker({ autoclose:true, todayHighlight:true, format:'yyyy-mm-dd' });
    $("#btnCari").click(function(){ currentTanggal = $("#tanggal").val(); lastId = 0; loadPresensi(); });
    $("#btnPrint").click(function(){ window.print(); });
    $("#btnToggleLive").click(toggleLive);

    loadPresensi();
    startAutoRefresh();
});

// ── Load Data ──
function loadPresensi() {
    var tanggal = currentTanggal;
    $("#loadpresensi").html('<tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Memuat data...</p></td></tr>');

    $.ajax({
        type: 'POST',
        url: '/presensi/getpresensi',
        data: { _token: "{{ csrf_token() }}", tanggal: tanggal },
        cache: false,
        success: function(html) {
            $("#loadpresensi").html(html);
            updateStats();
            updateLastId();
            updateTime();
        },
        error: function() {
            $("#loadpresensi").html('<tr><td colspan="9" class="text-center text-danger py-4">Gagal memuat data.</td></tr>');
        }
    });
}

// ── Update Stats ──
function updateStats() {
    var rows = $("#loadpresensi tr").length;
    var terlambat = $(".badge-terlambat").length;
    var pulang = 0;
    $("#loadpresensi tr").each(function(){
        if ($(this).find('td:eq(5)').text().trim() !== '' && !$(this).find('td:eq(5)').find('.badge').length) {
            pulang++;
        }
    });
    animateNum('statHadir', rows);
    animateNum('statTerlambat', terlambat);
    animateNum('statPulang', pulang);
    animateNum('statBelumPulang', Math.max(0, rows - pulang));
}

function animateNum(id, target) {
    var el = document.getElementById(id);
    if (!el) return;
    var current = parseInt(el.textContent) || 0;
    var step = target > current ? 1 : -1;
    if (current === target) return;
    var interval = setInterval(function(){
        current += step;
        el.textContent = current;
        if (current === target) clearInterval(interval);
    }, 30);
}

function updateLastId() {
    // Ambil ID terbesar dari baris yang ada
    var maxId = 0;
    $("#loadpresensi tr").each(function(){
        var id = parseInt($(this).data('id') || 0);
        if (id > maxId) maxId = id;
    });
    if (maxId > 0) lastId = maxId;
}

function updateTime() {
    var now = new Date();
    var h = String(now.getHours()).padStart(2,'0');
    var m = String(now.getMinutes()).padStart(2,'0');
    var s = String(now.getSeconds()).padStart(2,'0');
    document.getElementById('lastUpdate').textContent = h+':'+m+':'+s;
}

// ── Auto Refresh (polling) ──
function startAutoRefresh() {
    countdown = 30;
    clearInterval(countdownTimer);
    clearInterval(refreshTimer);

    countdownTimer = setInterval(function(){
        if (!isLive) return;
        countdown--;
        var el = document.getElementById('nextRefresh');
        if (el) el.textContent = '(refresh dalam ' + countdown + 's)';
        if (countdown <= 0) {
            countdown = 30;
            checkNewEntries();
        }
    }, 1000);
}

function checkNewEntries() {
    if (!isLive) return;
    $.ajax({
        type: 'GET',
        url: '/presensi/monitoring-count',
        data: { tanggal: currentTanggal, since_id: lastId },
        cache: false,
        success: function(data) {
            if (data.new_entries && data.new_entries.length > 0) {
                // Ada data baru — reload tabel
                loadPresensi();
                // Tampilkan notifikasi untuk setiap entri baru
                data.new_entries.forEach(function(entry) {
                    showNotification(entry);
                });
                lastId = data.last_id;
            } else {
                updateTime();
            }
        }
    });
}

// ── Notifikasi Toast ──
function showNotification(entry) {
    var container = document.getElementById('notifContainer');
    var toast = document.createElement('div');
    toast.className = 'notification-toast';
    toast.innerHTML =
        '<img src="' + entry.foto_url + '" style="width:40px;height:40px;border-radius:50%;object-fit:cover;flex-shrink:0;">' +
        '<div>' +
            '<div style="font-weight:700;font-size:.85rem;">' + entry.nama_lengkap + '</div>' +
            '<div style="font-size:.75rem;color:#6c757d;">' + entry.kelas + ' &bull; ' + (entry.jam_out ? 'Absen Pulang ' + entry.jam_out : 'Absen Masuk ' + entry.jam_in) + '</div>' +
        '</div>' +
        '<button onclick="this.parentElement.remove()" style="background:none;border:none;font-size:1.2rem;color:#999;cursor:pointer;margin-left:auto;">×</button>';
    container.appendChild(toast);
    setTimeout(function(){ if (toast.parentElement) toast.remove(); }, 5000);
}

// ── Toggle Live ──
function toggleLive() {
    isLive = !isLive;
    var dot = document.getElementById('liveDot');
    var label = document.getElementById('liveLabel');
    var btn = document.getElementById('btnToggleLive');
    var nr = document.getElementById('nextRefresh');
    if (isLive) {
        dot.style.background = '#2fb344';
        dot.style.animation = 'blink 1.5s infinite';
        label.textContent = 'Live';
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 5m0 1a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v12a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1z" /><path d="M14 5m0 1a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v12a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1z" /></svg> Pause';
        countdown = 30;
    } else {
        dot.style.background = '#adb5bd';
        dot.style.animation = 'none';
        label.textContent = 'Paused';
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 4v16l13 -8z" /></svg> Resume';
        if (nr) nr.textContent = '';
    }
}

// ── Tampilkan Peta ──
$(document).on('click', '.tampilkanpeta', function(e){
    e.preventDefault();
    var id = $(this).attr('data-id');
    $.ajax({
        type:'POST', url:'/tampilkanpeta',
        data:{ _token:"{{ csrf_token() }}", id:id },
        cache:false,
        success:function(respond){ $("#loadmap").html(respond); }
    });
    $("#modal-tampilkanpeta").modal("show");
});
</script>
@endpush
@endsection
