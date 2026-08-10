@extends('layouts.presensi')

@section('header')
<div class="appHeader" style="background:linear-gradient(135deg,#1a73e8,#0d47a1);color:#fff;">
    <div class="left">
        <a href="{{ route('dashboard') }}" class="headerButton" style="color:#fff;">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle" style="font-weight:700;">Absensi Wajah</div>
    <div class="right">
        <span id="jamHeader" style="font-size:.8rem;padding-right:10px;opacity:.9;"></span>
    </div>
</div>
<style>
/* -- Video Container -- */
#videoWrap {
    position: relative;
    background: #000;
    overflow: hidden;
    width: 100%;
    border-radius: 0 0 28px 28px;
}
#videoEl { width: 100%; display: block; }
#overlayCanvas {
    position: absolute; top: 0; left: 0;
    width: 100%; height: 100%; pointer-events: none;
}
/* -- Scan frame animasi -- */
#scanFrame {
    position: absolute; top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 200px; height: 200px;
    pointer-events: none;
}
.sf-corner {
    position: absolute; width: 28px; height: 28px;
    border-color: rgba(255,255,255,0.85); border-style: solid;
}
.sf-tl { top:0; left:0; border-width: 3px 0 0 3px; border-radius: 6px 0 0 0; }
.sf-tr { top:0; right:0; border-width: 3px 3px 0 0; border-radius: 0 6px 0 0; }
.sf-bl { bottom:0; left:0; border-width: 0 0 3px 3px; border-radius: 0 0 0 6px; }
.sf-br { bottom:0; right:0; border-width: 0 3px 3px 0; border-radius: 0 0 6px 0; }
@keyframes scanPulse {
    0%,100% { opacity: 0.6; transform: translate(-50%,-50%) scale(1); }
    50%      { opacity: 1;   transform: translate(-50%,-50%) scale(1.04); }
}
#scanFrame { animation: scanPulse 2s ease-in-out infinite; }
#scanFrame.matched {
    border-color: #2fb344;
    animation: none;
    opacity: 1;
}
/* -- Status pill -- */
#statusPill {
    position: absolute; bottom: 14px; left: 50%;
    transform: translateX(-50%);
    padding: 6px 18px; border-radius: 20px;
    font-size: 0.78rem; font-weight: 700;
    backdrop-filter: blur(8px);
    white-space: nowrap;
    transition: all 0.3s;
}
/* -- Match Card -- */
#matchCard {
    background: #fff;
    border-radius: 20px;
    padding: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.10);
    display: none;
    animation: slideUp 0.35s cubic-bezier(.4,0,.2,1);
}
@keyframes slideUp {
    from { transform: translateY(16px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
.match-avatar-wrap {
    position: relative;
    width: 72px; height: 72px; flex-shrink: 0;
}
.match-avatar-wrap img {
    width: 72px; height: 72px;
    border-radius: 50%; object-fit: cover;
    border: 3px solid #2fb344;
}
.match-avatar-wrap .verified-badge {
    position: absolute; bottom: 0; right: 0;
    width: 22px; height: 22px;
    background: #2fb344; border-radius: 50%;
    border: 2px solid #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; color: #fff;
}
.conf-bar-wrap {
    height: 7px; border-radius: 4px;
    background: #e9ecef; overflow: hidden; margin-top: 6px;
}
.conf-bar-fill {
    height: 100%; border-radius: 4px;
    transition: width 0.5s cubic-bezier(.4,0,.2,1);
}
/* -- Absen Button -- */
#btnAbsen {
    border-radius: 16px;
    padding: 15px;
    font-size: 1rem; font-weight: 700;
    letter-spacing: 0.3px;
    transition: all 0.2s;
    border: none;
}
#btnAbsen:not(:disabled) {
    box-shadow: 0 6px 20px rgba(26,115,232,0.35);
}
#btnAbsen:not(:disabled):active {
    transform: scale(0.97);
}
/* -- Map -- */
#map { height: 180px; border-radius: 16px; overflow: hidden; }
/* -- Model status -- */
#modelStatus {
    border-radius: 12px; padding: 11px 14px;
    font-size: 0.82rem;
}
/* -- Done card -- */
.done-card {
    background: linear-gradient(135deg, #34a853, #1e7e34);
    border-radius: 20px; padding: 24px; color: #fff;
    text-align: center;
    box-shadow: 0 6px 24px rgba(52,168,83,.35);
}
</style>
@endsection

@section('content')
@php
    $siswaLogin = Auth::guard('siswa')->user();
    // $fotoSiswa dan $hasFace sudah dikirim dari FaceRecognitionController
@endphp

<div style="padding-bottom: 16px;">

@if (!$hasFace)
{{-- -- Wajah belum terdaftar -- --}}
<div style="padding: 14px 12px 0;">
    <div style="background:#fff3cd; border-radius:16px; padding:16px; border-left:4px solid #f9a825;">
        <div class="d-flex align-items-start gap-3">
            <div style="font-size:2rem; flex-shrink:0;">
                <ion-icon name="alert-circle-outline" style="color:#f9a825;"></ion-icon>
            </div>
            <div>
                <div style="font-weight:700; color:#856404; font-size:.9rem;">Wajah Belum Terdaftar</div>
                <div style="font-size:.78rem; color:#856404; margin-top:4px; line-height:1.5;">
                    Hubungi admin untuk mendaftarkan data wajah kamu agar bisa absen menggunakan face recognition.
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if ($cek && !empty($cek->jam_out))
{{-- -- Sudah selesai absen -- --}}
<div style="padding: 14px 12px 0;">
    <div class="done-card">
        <div style="font-size:3rem; margin-bottom:8px;">
            <ion-icon name="checkmark-circle" style="color:#fff;"></ion-icon>
        </div>
        <div style="font-size:1.1rem; font-weight:800; margin-bottom:4px;">Presensi Hari Ini Selesai!</div>
        <div style="font-size:.82rem; opacity:.9; margin-bottom:16px;">Kamu sudah absen masuk dan pulang hari ini.</div>
        <div class="d-flex justify-content-center gap-4">
            <div>
                <div style="font-size:.68rem; opacity:.8;">Masuk</div>
                <div style="font-size:1.1rem; font-weight:800;">{{ $cek->jam_in }}</div>
            </div>
            <div style="width:1px; background:rgba(255,255,255,.3);"></div>
            <div>
                <div style="font-size:.68rem; opacity:.8;">Pulang</div>
                <div style="font-size:1.1rem; font-weight:800;">{{ $cek->jam_out }}</div>
            </div>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-light mt-4 w-100" style="border-radius:12px; font-weight:700;">
            Kembali ke Dashboard
        </a>
    </div>
</div>
@else

{{-- -- Model loading status -- --}}
<div style="padding: 10px 12px 0;">
    <div id="modelStatus" style="background:#eff6ff; color:#1e40af;">
        <div class="d-flex align-items-center gap-2">
            <div class="spinner-border spinner-border-sm" role="status"></div>
            <span>Memuat model AI face recognition...</span>
        </div>
    </div>
</div>

{{-- -- Video kamera -- --}}
<div id="videoWrap">
    <video id="videoEl" autoplay muted playsinline></video>
    <canvas id="overlayCanvas"></canvas>

    {{-- Scan frame corners -- --}}
    <div id="scanFrame">
        <div class="sf-corner sf-tl"></div>
        <div class="sf-corner sf-tr"></div>
        <div class="sf-corner sf-bl"></div>
        <div class="sf-corner sf-br"></div>
    </div>

    {{-- Status pill di atas video -- --}}
    <div id="statusPill" style="background:rgba(0,0,0,.55); color:#fff;">
        <ion-icon name="scan-outline" style="vertical-align:-2px; margin-right:4px;"></ion-icon>
        Memuat...
    </div>
</div>

{{-- -- Match Card (muncul saat wajah dikenali) -- --}}
<div style="padding: 12px 12px 0;">
    <div id="matchCard">
        <div class="d-flex align-items-center gap-3">
            {{-- Foto profil siswa -- --}}
            <div class="match-avatar-wrap">
                <img id="matchPhoto"
                     src="{{ $fotoSiswa }}"
                     alt="Foto Siswa"
                     onerror="this.src='{{ asset('assets/img/kosong.jpg') }}'">
                <div class="verified-badge">
                    <ion-icon name="checkmark" style="font-size:11px;"></ion-icon>
                </div>
            </div>
            {{-- Info siswa -- --}}
            <div style="flex:1; min-width:0;">
                <div id="matchName" style="font-size:1rem; font-weight:800; color:#1a1a1a;
                     white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></div>
                <div id="matchKelas" style="font-size:.78rem; color:#6c757d; margin-top:1px;"></div>
                <div id="matchJurusan" style="font-size:.72rem; color:#aaa;"></div>
                {{-- Confidence bar -- --}}
                <div class="conf-bar-wrap">
                    <div id="confFill" class="conf-bar-fill" style="width:0%; background:#2fb344;"></div>
                </div>
                <div id="confText" style="font-size:.7rem; color:#6c757d; margin-top:3px;"></div>
            </div>
            {{-- Status icon -- --}}
            <div id="matchStatusIcon" style="font-size:2rem; flex-shrink:0;"></div>
        </div>
    </div>
</div>

{{-- -- Tombol Absen -- --}}
<div style="padding: 12px 12px 0;">
    @if ($cek)
        <button id="btnAbsen" class="btn btn-danger btn-block w-100" disabled>
            <ion-icon name="log-out-outline" style="vertical-align:-2px; margin-right:6px;"></ion-icon>
            Absen Pulang
        </button>
    @else
        <button id="btnAbsen" class="btn btn-primary btn-block w-100" disabled>
            <ion-icon name="log-in-outline" style="vertical-align:-2px; margin-right:6px;"></ion-icon>
            Absen Masuk
        </button>
    @endif
</div>

{{-- -- Peta GPS -- --}}
<div style="padding: 12px 12px 0;">
    <div style="font-size:.72rem; font-weight:600; color:#888; text-transform:uppercase;
         letter-spacing:.5px; margin-bottom:6px;">
        <ion-icon name="location-outline" style="vertical-align:-2px;"></ion-icon> Lokasi Kamu
    </div>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <div id="map"></div>
    <div id="gpsStatus" style="font-size:.72rem; color:#6c757d; margin-top:5px; text-align:center;">
        <span class="spinner-border spinner-border-sm me-1" style="width:.7rem;height:.7rem;"></span>
        Mendapatkan lokasi GPS...
    </div>
</div>

@endif
</div>

<input type="hidden" id="lokasiInput">
@endsection

@push('myscripts')
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const MODEL_URL  = "{{ asset('face-models') }}";
const STORE_URL  = "{{ route('presensi.face.store') }}";
const DESC_URL   = "{{ route('face.descriptors') }}";
const CSRF_TOKEN = "{{ csrf_token() }}";
const IS_DONE    = {{ ($cek && !empty($cek->jam_out)) ? 'true' : 'false' }};
const IS_ABSEN   = {{ $cek ? 'true' : 'false' }};

if (!IS_DONE) {
    let videoEl, overlayCanvas, overlayCtx;
    let detectionInterval = null;
    let knownDescriptors  = [];
    let currentMatch      = null;
    let lokasi            = '';
    let mapInstance       = null;

    // -- GPS ------------------------------------------------------------------
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            pos => {
                lokasi = pos.coords.latitude + ',' + pos.coords.longitude;
                document.getElementById('lokasiInput').value = lokasi;
                document.getElementById('gpsStatus').innerHTML =
                    '<ion-icon name="checkmark-circle-outline" style="color:#34a853;vertical-align:-2px;"></ion-icon> ' +
                    'GPS aktif &bull; ' + pos.coords.latitude.toFixed(5) + ', ' + pos.coords.longitude.toFixed(5);

                mapInstance = L.map('map').setView([pos.coords.latitude, pos.coords.longitude], 16);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19, attribution: '© OpenStreetMap'
                }).addTo(mapInstance);
                L.marker([pos.coords.latitude, pos.coords.longitude])
                    .addTo(mapInstance)
                    .bindPopup('Lokasi kamu').openPopup();
                L.circle([{{ config('school.latitude') }}, {{ config('school.longitude') }}], {
                    color: '#1a73e8', fillColor: '#1a73e8', fillOpacity: 0.12, radius: {{ config('school.radius_meters') }}
                }).addTo(mapInstance).bindPopup('Area Sekolah');
            },
            err => {
                document.getElementById('gpsStatus').innerHTML =
                    '<ion-icon name="warning-outline" style="color:#ea4335;vertical-align:-2px;"></ion-icon> ' +
                    'GPS gagal: ' + err.message;
            },
            { enableHighAccuracy: true, timeout: 12000 }
        );
    }

    // -- Load Models & Descriptors ---------------------------------------------
    async function init() {
        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
            ]);

            const resp = await fetch(DESC_URL);
            const json = await resp.json();

            if (json.data && json.data.length > 0) {
                knownDescriptors = json.data.map(s => ({
                    nisn:         s.nisn,
                    nama_lengkap: s.nama_lengkap,
                    kelas:        s.kelas,
                    foto:         s.foto,   // URL foto profil dari storage
                    descriptor:   new Float32Array(s.face_descriptor),
                }));
            }

            const count = knownDescriptors.length;
            const ms = document.getElementById('modelStatus');

            // Cek apakah wajah siswa yang login ada di daftar
            const NISN_LOGIN = "{{ Auth::guard('siswa')->user()->nisn }}";
            
            // CRITICAL FIX: Konversi semua NISN ke string untuk memastikan perbandingan bekerja
            knownDescriptors = knownDescriptors.map(d => ({
                ...d,
                nisn: String(d.nisn)  // Pastikan NISN adalah string
            }));
            
            console.log('🔍 DEBUG - NISN Login:', NISN_LOGIN);
            console.log('🔍 DEBUG - Total descriptors loaded:', count);
            console.log('🔍 DEBUG - All NISN in database:', knownDescriptors.map(d => d.nisn));
            
            const myFace = knownDescriptors.find(d => d.nisn === NISN_LOGIN);
            
            console.log('🔍 DEBUG - My face found:', myFace ? 'YES' : 'NO');

            if (myFace) {
                // HANYA simpan wajah siswa yang login, buang yang lain
                knownDescriptors = [myFace];
                
                console.log('✅ SECURITY - Filtered to only logged-in student. Remaining:', knownDescriptors.length);
                
                ms.innerHTML = '<div class="d-flex align-items-center gap-2" style="color:#065f46;">' +
                    '<ion-icon name="checkmark-circle-outline" style="font-size:1.1rem;"></ion-icon>' +
                    '<span>Sistem siap &bull; Wajah kamu <strong>sudah terdaftar</strong></span></div>';
                ms.style.background = '#d1fae5';
            } else {
                // Wajah belum terdaftar - kosongkan array agar tidak ada yang dikenali
                knownDescriptors = [];
                
                console.log('⚠️ WARNING - Face not registered. Cleared all descriptors.');
                
                ms.innerHTML = '<div class="d-flex align-items-center gap-2" style="color:#92400e;">' +
                    '<ion-icon name="warning-outline" style="font-size:1.1rem;"></ion-icon>' +
                    '<span>Wajah kamu <strong>belum terdaftar</strong> di sistem</span></div>';
                ms.style.background = '#fef3c7';
            }

            startCamera();
        } catch (err) {
            const ms = document.getElementById('modelStatus');
            ms.innerHTML = '<strong>Gagal memuat model AI.</strong><br><small>' + err.message + '</small>';
            ms.style.background = '#fee2e2';
            ms.style.color = '#991b1b';
        }
    }

    // -- Kamera ---------------------------------------------------------------
    async function startCamera() {
        videoEl       = document.getElementById('videoEl');
        overlayCanvas = document.getElementById('overlayCanvas');
        overlayCtx    = overlayCanvas.getContext('2d');

        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' }
            });
            videoEl.srcObject = stream;
            videoEl.onloadedmetadata = () => {
                overlayCanvas.width  = videoEl.videoWidth;
                overlayCanvas.height = videoEl.videoHeight;
                startDetection();
            };
        } catch (err) {
            swal('Kamera Error', 'Tidak dapat mengakses kamera: ' + err.message, 'error');
        }
    }

    // -- Euclidean distance ----------------------------------------------------
    function euclideanDistance(a, b) {
        let sum = 0;
        for (let i = 0; i < a.length; i++) sum += (a[i] - b[i]) ** 2;
        return Math.sqrt(sum);
    }

    function findBestMatch(descriptor) {
        if (!knownDescriptors.length) return null;
        let best = null, bestDist = Infinity;
        for (const k of knownDescriptors) {
            const d = euclideanDistance(descriptor, k.descriptor);
            if (d < bestDist) { bestDist = d; best = { ...k, distance: d }; }
        }
        return best;
    }

    // -- Deteksi ---------------------------------------------------------------
    function setStatusPill(text, bg, color) {
        const p = document.getElementById('statusPill');
        p.style.background = bg;
        p.style.color = color;
        p.innerHTML = text;
    }

    function startDetection() {
        setStatusPill(
            '<ion-icon name="scan-outline" style="vertical-align:-2px;margin-right:4px;"></ion-icon> Mendeteksi wajah...',
            'rgba(0,0,0,.55)', '#fff'
        );

        detectionInterval = setInterval(async () => {
            if (!videoEl || videoEl.readyState < 2) return;

            const det = await faceapi
                .detectSingleFace(videoEl, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
                .withFaceLandmarks()
                .withFaceDescriptor();

            overlayCtx.clearRect(0, 0, overlayCanvas.width, overlayCanvas.height);

            if (!det) {
                setStatusPill(
                    '<ion-icon name="scan-outline" style="vertical-align:-2px;margin-right:4px;"></ion-icon> Arahkan wajah ke kamera',
                    'rgba(0,0,0,.55)', '#fff'
                );
                hideMatchCard();
                document.getElementById('btnAbsen').disabled = true;
                document.getElementById('scanFrame').className = '';
                currentMatch = null;
                return;
            }

            const box    = det.detection.box;
            const scaleX = overlayCanvas.width  / videoEl.videoWidth;
            const scaleY = overlayCanvas.height / videoEl.videoHeight;
            const desc   = Array.from(det.descriptor);
            const match  = findBestMatch(desc);
            const THRESH = 0.55;
            const isMatch = match && match.distance < THRESH;

            // Gambar kotak deteksi
            const bx = box.x * scaleX, by = box.y * scaleY;
            const bw = box.width * scaleX, bh = box.height * scaleY;

            overlayCtx.strokeStyle = isMatch ? '#2fb344' : '#f59e0b';
            overlayCtx.lineWidth   = 3;
            overlayCtx.strokeRect(bx, by, bw, bh);

            // Corner accent
            const cs = 18;
            overlayCtx.lineWidth = 4;
            [[bx,by],[bx+bw,by],[bx,by+bh],[bx+bw,by+bh]].forEach(([cx,cy], i) => {
                overlayCtx.beginPath();
                if (i===0) { overlayCtx.moveTo(cx+cs,cy); overlayCtx.lineTo(cx,cy); overlayCtx.lineTo(cx,cy+cs); }
                if (i===1) { overlayCtx.moveTo(cx-cs,cy); overlayCtx.lineTo(cx,cy); overlayCtx.lineTo(cx,cy+cs); }
                if (i===2) { overlayCtx.moveTo(cx+cs,cy); overlayCtx.lineTo(cx,cy); overlayCtx.lineTo(cx,cy-cs); }
                if (i===3) { overlayCtx.moveTo(cx-cs,cy); overlayCtx.lineTo(cx,cy); overlayCtx.lineTo(cx,cy-cs); }
                overlayCtx.strokeStyle = isMatch ? '#2fb344' : '#f59e0b';
                overlayCtx.stroke();
            });

            if (isMatch) {
                // CRITICAL SECURITY CHECK: Pastikan NISN yang match adalah NISN yang login
                const NISN_LOGIN = "{{ Auth::guard('siswa')->user()->nisn }}";
                
                console.log('🔒 SECURITY CHECK - Match NISN:', match.nisn, 'Login NISN:', NISN_LOGIN);
                
                if (String(match.nisn) !== String(NISN_LOGIN)) {
                    console.error('❌ SECURITY VIOLATION - Matched face does not belong to logged-in user!');
                    console.error('   Matched:', match.nisn, '(' + match.nama_lengkap + ')');
                    console.error('   Expected:', NISN_LOGIN);
                    
                    // Treat as no match - security violation
                    currentMatch = null;
                    setStatusPill(
                        '<ion-icon name="warning-outline" style="vertical-align:-2px;margin-right:4px;"></ion-icon> Wajah Tidak Cocok!',
                        'rgba(234,67,53,.85)', '#fff'
                    );
                    document.getElementById('scanFrame').className = '';
                    hideMatchCard();
                    document.getElementById('btnAbsen').disabled = true;
                    
                    // Label di canvas
                    overlayCtx.fillStyle = 'rgba(234,67,53,.85)';
                    overlayCtx.fillRect(bx, by - 28, 180, 26);
                    overlayCtx.fillStyle = '#fff';
                    overlayCtx.font = 'bold 13px Inter, sans-serif';
                    overlayCtx.fillText('✗ Bukan Wajah Anda!', bx + 6, by - 9);
                    return;
                }
                
                currentMatch = { ...match, descriptor: desc };

                // Confidence 0-100 (jarak 0=100%, jarak 0.55=0%)
                const conf = Math.round(Math.max(0, (1 - match.distance / THRESH)) * 100);
                
                console.log('✅ MATCH SUCCESS - Distance:', match.distance.toFixed(4), 'Confidence:', conf + '%');

                setStatusPill(
                    '<ion-icon name="checkmark-circle-outline" style="vertical-align:-2px;margin-right:4px;"></ion-icon> Wajah Dikenali!',
                    'rgba(47,179,68,.9)', '#fff'
                );
                document.getElementById('scanFrame').className = 'matched';

                // Tampilkan match card dengan foto profil siswa
                showMatchCard(match, conf);
                document.getElementById('btnAbsen').disabled = false;

                // Label nama di canvas
                overlayCtx.fillStyle = 'rgba(47,179,68,.85)';
                const labelW = Math.min(bw, 200);
                overlayCtx.fillRect(bx, by - 28, labelW, 26);
                overlayCtx.fillStyle = '#fff';
                overlayCtx.font = 'bold 13px Inter, sans-serif';
                overlayCtx.fillText('✓ ' + match.nama_lengkap, bx + 6, by - 9);

            } else {
                currentMatch = null;
                setStatusPill(
                    '<ion-icon name="close-circle-outline" style="vertical-align:-2px;margin-right:4px;"></ion-icon> Wajah Tidak Dikenali',
                    'rgba(234,67,53,.85)', '#fff'
                );
                document.getElementById('scanFrame').className = '';
                hideMatchCard();
                document.getElementById('btnAbsen').disabled = true;

                // Label di canvas
                overlayCtx.fillStyle = 'rgba(245,158,11,.85)';
                overlayCtx.fillRect(bx, by - 28, 150, 26);
                overlayCtx.fillStyle = '#fff';
                overlayCtx.font = 'bold 13px Inter, sans-serif';
                overlayCtx.fillText('✗ Tidak Dikenali', bx + 6, by - 9);
            }
        }, 400);
    }

    // -- Match Card helpers ----------------------------------------------------
    function showMatchCard(match, conf) {
        const card = document.getElementById('matchCard');
        card.style.display = 'block';

        document.getElementById('matchName').textContent  = match.nama_lengkap;
        document.getElementById('matchKelas').textContent = 'Kelas ' + match.kelas;

        // Set foto profil siswa yang dikenali
        // match.foto = absolute URL dari url('storage/uploads/foto_siswa/...') di controller
        const photoEl = document.getElementById('matchPhoto');
        if (match.foto && match.foto !== '' && match.foto !== 'null' && match.foto !== null) {
            photoEl.src = match.foto;
        } else {
            // Tidak ada foto profil ? pakai foto default siswa yang login
            photoEl.src = "{{ $fotoSiswa }}";
        }

        // Confidence bar
        const fill = document.getElementById('confFill');
        fill.style.width = conf + '%';
        fill.style.background = conf >= 70 ? '#2fb344' : conf >= 50 ? '#f9a825' : '#ea4335';

        document.getElementById('confText').textContent = 'Kecocokan: ' + conf + '%';
        document.getElementById('matchStatusIcon').innerHTML = conf >= 70 
            ? '<ion-icon name="checkmark-circle" style="color:#2fb344;"></ion-icon>' 
            : '<ion-icon name="warning" style="color:#f9a825;"></ion-icon>';

        // Border warna sesuai confidence
        document.querySelector('.match-avatar-wrap img').style.borderColor =
            conf >= 70 ? '#2fb344' : '#f9a825';
    }

    function hideMatchCard() {
        document.getElementById('matchCard').style.display = 'none';
    }

    // -- Tombol Absen ---------------------------------------------------------
    document.getElementById('btnAbsen').addEventListener('click', async () => {
        if (!currentMatch) {
            swal('Perhatian', 'Wajah belum dikenali!', 'warning');
            return;
        }
        if (!lokasi) {
            swal('Perhatian', 'Lokasi GPS belum tersedia. Tunggu sebentar.', 'warning');
            return;
        }

        clearInterval(detectionInterval);

        // Snapshot kamera
        const snap = document.createElement('canvas');
        snap.width  = videoEl.videoWidth;
        snap.height = videoEl.videoHeight;
        snap.getContext('2d').drawImage(videoEl, 0, 0);
        const imageData = snap.toDataURL('image/jpeg', 0.85);

        const btn = document.getElementById('btnAbsen');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';

        try {
            const response = await fetch(STORE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({
                    nisn_match: currentMatch.nisn,
                    confidence: currentMatch.distance,
                    lokasi:     lokasi,
                    image:      imageData,
                }),
            });

            const result = await response.json();

            if (result.status === 'success') {
                swal('SUKSES!', result.message, 'success');
                setTimeout(() => { window.location.href = '/dashboard'; }, 2000);
            } else {
                swal('GAGAL!', result.message || 'Absensi gagal!', 'error');
                btn.disabled = false;
                btn.innerHTML = IS_ABSEN
                    ? '<ion-icon name="log-out-outline" style="vertical-align:-2px;margin-right:6px;"></ion-icon> Absen Pulang'
                    : '<ion-icon name="log-in-outline" style="vertical-align:-2px;margin-right:6px;"></ion-icon> Absen Masuk';
                startDetection();
            }
        } catch (err) {
            swal('ERROR!', 'Terjadi kesalahan: ' + err.message, 'error');
            btn.disabled = false;
            startDetection();
        }
    });

    init();
}

// -- Jam di header ----------------------------------------------------------
(function tickClock() {
    var el = document.getElementById('jamHeader');
    if (el) {
        var now = new Date();
        var h = String(now.getHours()).padStart(2,'0');
        var m = String(now.getMinutes()).padStart(2,'0');
        var s = String(now.getSeconds()).padStart(2,'0');
        el.textContent = h + ':' + m + ':' + s;
    }
    setTimeout(tickClock, 1000);
})();
</script>
@endpush
