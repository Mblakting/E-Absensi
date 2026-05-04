@extends('layouts.presensi')

@section('header')
<!-- App Header -->
<div class="appHeader bg-primary text-light">
    <div class="left">
        <a href="javascript:;" class="headerButton goBack">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle">Absensi Wajah</div>
    <div class="right"></div>
</div>
<!-- * App Header -->

<style>
    #videoContainer {
        position: relative;
        background: #000;
        border-radius: 16px;
        overflow: hidden;
        width: 100%;
    }
    #videoEl {
        width: 100%;
        display: block;
        border-radius: 16px;
    }
    #overlayCanvas {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        pointer-events: none;
    }
    .status-card {
        border-radius: 12px;
        padding: 12px 16px;
        margin-top: 12px;
        font-size: 0.9rem;
    }
    .face-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .face-badge.detected { background: #d1fae5; color: #065f46; }
    .face-badge.searching { background: #fef3c7; color: #92400e; }
    .face-badge.matched { background: #dbeafe; color: #1e40af; }
    .face-badge.no-face { background: #fee2e2; color: #991b1b; }
    #btnAbsen {
        border-radius: 50px;
        padding: 14px;
        font-size: 1rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .confidence-bar {
        height: 6px;
        border-radius: 3px;
        background: #e5e7eb;
        overflow: hidden;
        margin-top: 6px;
    }
    .confidence-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.3s ease;
    }
</style>
@endsection

@section('content')
<div style="margin-top: 70px; padding: 12px;">

    @if (!$hasFace)
    <!-- Peringatan wajah belum terdaftar -->
    <div class="alert alert-warning" style="border-radius:12px;">
        <div class="d-flex align-items-start gap-2">
            <ion-icon name="warning-outline" style="font-size:1.4rem; flex-shrink:0; margin-top:2px;"></ion-icon>
            <div>
                <strong>Wajah Belum Terdaftar</strong><br>
                <small>Hubungi admin untuk mendaftarkan data wajah kamu terlebih dahulu agar bisa absen menggunakan face recognition.</small>
            </div>
        </div>
    </div>
    @endif

    @if ($cek && !empty($cek->jam_out))
    <!-- Sudah absen masuk dan pulang -->
    <div class="alert alert-success" style="border-radius:12px;">
        <div class="d-flex align-items-center gap-2">
            <ion-icon name="checkmark-circle-outline" style="font-size:1.4rem;"></ion-icon>
            <div>
                <strong>Presensi Hari Ini Selesai</strong><br>
                <small>Masuk: {{ $cek->jam_in }} &nbsp;|&nbsp; Pulang: {{ $cek->jam_out }}</small>
            </div>
        </div>
    </div>
    @else

    <!-- Status loading model -->
    <div id="modelStatus" class="status-card" style="background:#eff6ff; color:#1e40af;">
        <div class="d-flex align-items-center gap-2">
            <div class="spinner-border spinner-border-sm" role="status"></div>
            <span>Memuat model AI... Harap tunggu.</span>
        </div>
    </div>

    <!-- Kamera -->
    <div id="videoContainer" class="mt-3">
        <video id="videoEl" autoplay muted playsinline></video>
        <canvas id="overlayCanvas"></canvas>
    </div>

    <!-- Status deteksi -->
    <div class="mt-2 text-center">
        <span id="faceBadge" class="face-badge searching">
            <ion-icon name="scan-outline"></ion-icon>
            Memuat...
        </span>
    </div>

    <!-- Info siswa yang dikenali -->
    <div id="matchInfo" style="display:none;" class="status-card mt-2" style="background:#eff6ff;">
        <div class="d-flex align-items-center gap-3">
            <img id="matchPhoto" src="" width="48" height="48"
                 style="border-radius:50%; object-fit:cover; border:2px solid #3b82f6;">
            <div>
                <div id="matchName" class="fw-bold" style="font-size:0.95rem;"></div>
                <div id="matchKelas" class="text-muted" style="font-size:0.8rem;"></div>
                <div class="confidence-bar mt-1" style="width:120px;">
                    <div id="confidenceFill" class="confidence-fill" style="background:#3b82f6; width:0%;"></div>
                </div>
                <div id="confidenceText" style="font-size:0.75rem; color:#6b7280;"></div>
            </div>
        </div>
    </div>

    <!-- Tombol absen -->
    <div class="mt-3">
        @if ($cek)
            <button id="btnAbsen" class="btn btn-danger btn-block" disabled>
                <ion-icon name="log-out-outline"></ion-icon> Absen Pulang
            </button>
        @else
            <button id="btnAbsen" class="btn btn-primary btn-block" disabled>
                <ion-icon name="log-in-outline"></ion-icon> Absen Masuk
            </button>
        @endif
    </div>

    <!-- Peta lokasi -->
    <div class="mt-3">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <div id="map" style="height:200px; border-radius:12px;"></div>
    </div>

    @endif
</div>

<input type="hidden" id="lokasiInput">
@endsection

@push('myscripts')
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
const MODEL_URL    = "{{ asset('face-models') }}";
const STORE_URL    = "{{ route('presensi.face.store') }}";
const DESC_URL     = "{{ route('face.descriptors') }}";
const CSRF_TOKEN   = "{{ csrf_token() }}";
const HAS_FACE     = {{ $hasFace ? 'true' : 'false' }};
const ALREADY_DONE = {{ ($cek && !empty($cek->jam_out)) ? 'true' : 'false' }};

if (ALREADY_DONE) {
    // Tidak perlu inisialisasi apapun
} else {
    let videoEl, overlayCanvas, overlayCtx;
    let detectionInterval = null;
    let knownDescriptors  = []; // [{nisn, nama_lengkap, kelas, foto, descriptor: Float32Array}]
    let currentMatch      = null; // {nisn, nama_lengkap, kelas, foto, distance}
    let lokasi            = '';

    // ─── GPS ────────────────────────────────────────────────────────────────
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            pos => {
                lokasi = pos.coords.latitude + ',' + pos.coords.longitude;
                document.getElementById('lokasiInput').value = lokasi;

                // Tampilkan peta
                const map = L.map('map').setView([pos.coords.latitude, pos.coords.longitude], 16);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(map);
                L.marker([pos.coords.latitude, pos.coords.longitude]).addTo(map);
                L.circle([-7.671325, 110.589607], {
                    color: 'green', fillColor: 'green', fillOpacity: 0.2, radius: 200
                }).addTo(map);
            },
            err => {
                swal('GPS Error', 'Gagal mendapatkan lokasi: ' + err.message, 'warning');
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }

    // ─── Load Models & Descriptors ──────────────────────────────────────────
    async function init() {
        try {
            // Load model AI
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
            ]);

            // Ambil descriptor dari server
            const resp = await fetch(DESC_URL);
            const json = await resp.json();

            if (json.data && json.data.length > 0) {
                knownDescriptors = json.data.map(s => ({
                    nisn:         s.nisn,
                    nama_lengkap: s.nama_lengkap,
                    kelas:        s.kelas,
                    foto:         s.foto,
                    descriptor:   new Float32Array(s.face_descriptor),
                }));
            }

            const count = knownDescriptors.length;
            document.getElementById('modelStatus').innerHTML =
                `<div class="d-flex align-items-center gap-2" style="color:#065f46;">
                    <ion-icon name="checkmark-circle-outline" style="font-size:1.2rem;"></ion-icon>
                    <span>Model siap &bull; ${count} wajah terdaftar</span>
                </div>`;
            document.getElementById('modelStatus').style.background = '#d1fae5';

            startCamera();
        } catch (err) {
            document.getElementById('modelStatus').innerHTML =
                `<strong>Gagal memuat model AI.</strong><br><small>${err.message}</small>`;
            document.getElementById('modelStatus').style.background = '#fee2e2';
            document.getElementById('modelStatus').style.color = '#991b1b';
        }
    }

    // ─── Kamera ─────────────────────────────────────────────────────────────
    async function startCamera() {
        videoEl       = document.getElementById('videoEl');
        overlayCanvas = document.getElementById('overlayCanvas');
        overlayCtx    = overlayCanvas.getContext('2d');

        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { width: 480, height: 360, facingMode: 'user' }
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

    // ─── Deteksi & Pencocokan ────────────────────────────────────────────────
    function euclideanDistance(a, b) {
        let sum = 0;
        for (let i = 0; i < a.length; i++) sum += (a[i] - b[i]) ** 2;
        return Math.sqrt(sum);
    }

    function findBestMatch(descriptor) {
        if (knownDescriptors.length === 0) return null;
        let best = null, bestDist = Infinity;
        for (const known of knownDescriptors) {
            const dist = euclideanDistance(descriptor, known.descriptor);
            if (dist < bestDist) {
                bestDist = dist;
                best = { ...known, distance: dist };
            }
        }
        return best;
    }

    function startDetection() {
        const badge = document.getElementById('faceBadge');
        badge.className = 'face-badge searching';
        badge.innerHTML = '<ion-icon name="scan-outline"></ion-icon> Mendeteksi wajah...';

        detectionInterval = setInterval(async () => {
            if (!videoEl || videoEl.readyState < 2) return;

            const detection = await faceapi
                .detectSingleFace(videoEl, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
                .withFaceLandmarks()
                .withFaceDescriptor();

            overlayCtx.clearRect(0, 0, overlayCanvas.width, overlayCanvas.height);

            if (!detection) {
                badge.className = 'face-badge searching';
                badge.innerHTML = '<ion-icon name="scan-outline"></ion-icon> Arahkan wajah ke kamera';
                document.getElementById('matchInfo').style.display = 'none';
                document.getElementById('btnAbsen').disabled = true;
                currentMatch = null;
                return;
            }

            const box    = detection.detection.box;
            const scaleX = overlayCanvas.width  / videoEl.videoWidth;
            const scaleY = overlayCanvas.height / videoEl.videoHeight;
            const desc   = Array.from(detection.descriptor);
            const match  = findBestMatch(desc);

            // Threshold: < 0.55 = cocok
            const THRESHOLD = 0.55;
            const isMatch   = match && match.distance < THRESHOLD;

            // Warna kotak
            overlayCtx.strokeStyle = isMatch ? '#2fb344' : '#f59e0b';
            overlayCtx.lineWidth   = 3;
            overlayCtx.strokeRect(
                box.x * scaleX, box.y * scaleY,
                box.width * scaleX, box.height * scaleY
            );

            if (isMatch) {
                currentMatch = { ...match, descriptor: desc };

                // Badge
                badge.className = 'face-badge matched';
                badge.innerHTML = '<ion-icon name="person-circle-outline"></ion-icon> Wajah Dikenali';

                // Info match
                const confidence = Math.max(0, Math.min(100, Math.round((1 - match.distance / 1.0) * 100)));
                document.getElementById('matchInfo').style.display = 'block';
                document.getElementById('matchName').textContent   = match.nama_lengkap;
                document.getElementById('matchKelas').textContent  = 'Kelas ' + match.kelas;
                document.getElementById('confidenceText').textContent = `Kecocokan: ${confidence}%`;
                document.getElementById('confidenceFill').style.width = confidence + '%';
                document.getElementById('confidenceFill').style.background = confidence > 70 ? '#2fb344' : '#f59e0b';

                if (match.foto) {
                    document.getElementById('matchPhoto').src = match.foto;
                }

                // Label di canvas
                overlayCtx.fillStyle = '#2fb344';
                overlayCtx.fillRect(box.x * scaleX, (box.y * scaleY) - 26, 180, 26);
                overlayCtx.fillStyle = '#fff';
                overlayCtx.font = '13px sans-serif';
                overlayCtx.fillText('✓ ' + match.nama_lengkap, (box.x * scaleX) + 6, (box.y * scaleY) - 8);

                document.getElementById('btnAbsen').disabled = false;
            } else {
                currentMatch = null;
                badge.className = 'face-badge no-face';
                badge.innerHTML = '<ion-icon name="close-circle-outline"></ion-icon> Wajah Tidak Dikenali';
                document.getElementById('matchInfo').style.display = 'none';
                document.getElementById('btnAbsen').disabled = true;

                // Label di canvas
                overlayCtx.fillStyle = '#f59e0b';
                overlayCtx.fillRect(box.x * scaleX, (box.y * scaleY) - 26, 160, 26);
                overlayCtx.fillStyle = '#fff';
                overlayCtx.font = '13px sans-serif';
                overlayCtx.fillText('? Tidak Dikenali', (box.x * scaleX) + 6, (box.y * scaleY) - 8);
            }
        }, 400);
    }

    // ─── Tombol Absen ────────────────────────────────────────────────────────
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

        // Ambil snapshot
        const snapCanvas = document.createElement('canvas');
        snapCanvas.width  = videoEl.videoWidth;
        snapCanvas.height = videoEl.videoHeight;
        snapCanvas.getContext('2d').drawImage(videoEl, 0, 0);
        const imageData = snapCanvas.toDataURL('image/jpeg', 0.85);

        const btn = document.getElementById('btnAbsen');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

        try {
            const response = await fetch(STORE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify({
                    nisn_match:  currentMatch.nisn,
                    confidence:  currentMatch.distance,
                    lokasi:      lokasi,
                    image:       imageData,
                }),
            });

            const result = await response.json();

            if (result.status === 'success') {
                swal('SUKSES!', result.message, 'success');
                setTimeout(() => { window.location.href = '/dashboard'; }, 2000);
            } else {
                swal('GAGAL!', result.message || 'Absensi gagal!', 'error');
                btn.disabled = false;
                btn.innerHTML = result.type === 'out'
                    ? '<ion-icon name="log-out-outline"></ion-icon> Absen Pulang'
                    : '<ion-icon name="log-in-outline"></ion-icon> Absen Masuk';
                startDetection();
            }
        } catch (err) {
            swal('ERROR!', 'Terjadi kesalahan: ' + err.message, 'error');
            btn.disabled = false;
            startDetection();
        }
    });

    // Mulai
    init();
}
</script>
@endpush
