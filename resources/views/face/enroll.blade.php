@extends('layouts.admin.tabler')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-2 text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 8v-2a2 2 0 0 1 2 -2h2" /><path d="M4 16v2a2 2 0 0 0 2 2h2" /><path d="M16 4h2a2 2 0 0 1 2 2v2" /><path d="M16 20h2a2 2 0 0 0 2 -2v-2" /><path d="M9 10a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" /><path d="M13 10a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" /><path d="M9.5 15a3.5 3.5 0 0 0 5 0" /></svg>
                    Daftarkan Wajah Siswa
                </h2>
                <p class="text-muted mt-1">Rekam data wajah untuk absensi face recognition</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
                    Kembali ke Data Siswa
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row">
            <!-- Info Siswa -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        @php
                            $fotoPath = public_path('storage/uploads/foto_siswa/' . ($siswa->foto ?? ''));
                        @endphp
                        @if (!empty($siswa->foto) && file_exists($fotoPath))
                            <img src="{{ asset('storage/uploads/foto_siswa/' . $siswa->foto) }}"
                                 class="avatar avatar-xl rounded-circle mb-3" alt="{{ $siswa->nama_lengkap }}">
                        @else
                            <img src="{{ asset('assets/img/kosong.jpg') }}"
                                 class="avatar avatar-xl rounded-circle mb-3" alt="No Photo">
                        @endif
                        <h3 class="card-title mb-1">{{ $siswa->nama_lengkap }}</h3>
                        <p class="text-muted mb-1">NISN: <strong>{{ $siswa->nisn }}</strong></p>
                        <p class="text-muted mb-1">Kelas: <strong>{{ $siswa->kelas }}</strong></p>
                        <p class="text-muted mb-3">Jurusan: <strong>{{ $siswa->nama_jurusan }}</strong></p>

                        @if (!empty($siswa->face_descriptor))
                            <div class="alert alert-success py-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                Wajah sudah terdaftar
                            </div>
                            <button id="btnHapusFace" class="btn btn-sm btn-outline-danger w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                Hapus Data Wajah
                            </button>
                        @else
                            <div class="alert alert-warning py-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
                                Wajah belum terdaftar
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Panduan -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="card-title">Panduan Pendaftaran</h4>
                    </div>
                    <div class="card-body">
                        <ol class="ps-3 mb-0" style="font-size:0.875rem;">
                            <li class="mb-2">Pastikan wajah siswa terlihat jelas di kamera</li>
                            <li class="mb-2">Pencahayaan harus cukup terang</li>
                            <li class="mb-2">Posisikan wajah di tengah frame</li>
                            <li class="mb-2">Tunggu hingga kotak deteksi muncul (warna hijau)</li>
                            <li class="mb-2">Klik <strong>"Ambil & Simpan Wajah"</strong></li>
                            <li>Ulangi jika hasilnya kurang baik</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Kamera & Proses -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Kamera Pendaftaran Wajah</h4>
                    </div>
                    <div class="card-body">
                        <!-- Status loading model -->
                        <div id="modelStatus" class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                <span>Memuat model AI face recognition... Harap tunggu.</span>
                            </div>
                        </div>

                        <!-- Container kamera -->
                        <div class="position-relative" style="background:#000; border-radius:8px; overflow:hidden;">
                            <video id="videoEl" autoplay muted playsinline
                                   style="width:100%; display:block; border-radius:8px;"></video>
                            <canvas id="overlayCanvas"
                                    style="position:absolute; top:0; left:0; width:100%; height:100%;"></canvas>
                        </div>

                        <!-- Status deteksi -->
                        <div id="detectionStatus" class="mt-3 text-center text-muted" style="display:none;">
                            <span id="detectionText">Mendeteksi wajah...</span>
                        </div>

                        <!-- Tombol aksi -->
                        <div class="row mt-3">
                            <div class="col-6">
                                <button id="btnCapture" class="btn btn-primary w-100" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 7h1a2 2 0 0 0 2 -2a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1a2 2 0 0 0 2 2h1a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2" /><path d="M9 13a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                                    Ambil & Simpan Wajah
                                </button>
                            </div>
                            <div class="col-6">
                                <button id="btnRetake" class="btn btn-outline-secondary w-100" style="display:none;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                                    Ulangi
                                </button>
                            </div>
                        </div>

                        <!-- Preview hasil capture -->
                        <div id="capturePreview" class="mt-3" style="display:none;">
                            <p class="text-muted mb-2">Preview wajah yang diambil:</p>
                            <canvas id="captureCanvas" style="border-radius:8px; max-width:200px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('myscript')
{{-- face-api.js dari CDN --}}
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
const NISN = "{{ $siswa->nisn }}";
const MODEL_URL = "{{ asset('face-models') }}";
const SAVE_URL  = "{{ route('face.save') }}";
const DELETE_URL = "{{ route('face.delete') }}";
const CSRF_TOKEN = "{{ csrf_token() }}";

let videoEl, overlayCanvas, overlayCtx;
let detectionInterval = null;
let currentDescriptor = null;
let faceDetected = false;

async function loadModels() {
    try {
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
        ]);
        document.getElementById('modelStatus').innerHTML =
            '<div class="d-flex align-items-center text-success"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg><span>Model AI siap! Arahkan wajah ke kamera.</span></div>';
        document.getElementById('modelStatus').className = 'alert alert-success';
        startCamera();
    } catch (err) {
        document.getElementById('modelStatus').innerHTML =
            '<strong>Gagal memuat model AI.</strong> Pastikan file model tersedia di folder <code>public/face-models/</code>.<br><small>' + err.message + '</small>';
        document.getElementById('modelStatus').className = 'alert alert-danger';
    }
}

async function startCamera() {
    videoEl = document.getElementById('videoEl');
    overlayCanvas = document.getElementById('overlayCanvas');
    overlayCtx = overlayCanvas.getContext('2d');

    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { width: 640, height: 480, facingMode: 'user' }
        });
        videoEl.srcObject = stream;
        videoEl.onloadedmetadata = () => {
            overlayCanvas.width  = videoEl.videoWidth;
            overlayCanvas.height = videoEl.videoHeight;
            document.getElementById('detectionStatus').style.display = 'block';
            startDetection();
        };
    } catch (err) {
        Swal.fire('Error', 'Tidak dapat mengakses kamera: ' + err.message, 'error');
    }
}

function startDetection() {
    detectionInterval = setInterval(async () => {
        if (!videoEl || videoEl.readyState < 2) return;

        const detection = await faceapi
            .detectSingleFace(videoEl, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
            .withFaceLandmarks()
            .withFaceDescriptor();

        overlayCtx.clearRect(0, 0, overlayCanvas.width, overlayCanvas.height);

        if (detection) {
            faceDetected = true;
            currentDescriptor = Array.from(detection.descriptor);

            // Gambar kotak deteksi
            const box = detection.detection.box;
            const scaleX = overlayCanvas.width  / videoEl.videoWidth;
            const scaleY = overlayCanvas.height / videoEl.videoHeight;

            overlayCtx.strokeStyle = '#2fb344';
            overlayCtx.lineWidth   = 3;
            overlayCtx.strokeRect(
                box.x * scaleX, box.y * scaleY,
                box.width * scaleX, box.height * scaleY
            );

            // Label
            overlayCtx.fillStyle = '#2fb344';
            overlayCtx.fillRect(box.x * scaleX, (box.y * scaleY) - 24, 120, 24);
            overlayCtx.fillStyle = '#fff';
            overlayCtx.font = '14px Inter, sans-serif';
            overlayCtx.fillText('Wajah Terdeteksi ✓', (box.x * scaleX) + 5, (box.y * scaleY) - 6);

            document.getElementById('detectionText').textContent = '✅ Wajah terdeteksi! Siap diambil.';
            document.getElementById('detectionText').className = 'text-success fw-bold';
            document.getElementById('btnCapture').disabled = false;
        } else {
            faceDetected = false;
            currentDescriptor = null;
            document.getElementById('detectionText').textContent = '🔍 Mendeteksi wajah... Arahkan wajah ke kamera.';
            document.getElementById('detectionText').className = 'text-muted';
            document.getElementById('btnCapture').disabled = true;
        }
    }, 300);
}

// Ambil & simpan wajah
document.getElementById('btnCapture').addEventListener('click', async () => {
    if (!currentDescriptor || !faceDetected) {
        Swal.fire('Perhatian', 'Wajah belum terdeteksi. Pastikan wajah terlihat jelas.', 'warning');
        return;
    }

    clearInterval(detectionInterval);

    // Ambil snapshot dari video
    const captureCanvas = document.getElementById('captureCanvas');
    captureCanvas.width  = videoEl.videoWidth;
    captureCanvas.height = videoEl.videoHeight;
    captureCanvas.getContext('2d').drawImage(videoEl, 0, 0);
    // Ambil base64 snapshot untuk disimpan sebagai foto profil
    const fotoSnapshot = captureCanvas.toDataURL('image/jpeg', 0.85);

    document.getElementById('capturePreview').style.display = 'block';
    document.getElementById('btnCapture').style.display = 'none';
    document.getElementById('btnRetake').style.display = 'block';

    const btn = document.getElementById('btnCapture');
    btn.disabled = true;

    try {
        const response = await fetch(SAVE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify({
                nisn:          NISN,
                descriptor:    currentDescriptor,
                foto_snapshot: fotoSnapshot,   // kirim snapshot wajah sebagai foto profil
            }),
        });

        const result = await response.json();

        if (result.status === 'success') {
            Swal.fire({
                title: 'Berhasil!',
                text: result.message,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire('Gagal', result.message, 'error');
            document.getElementById('btnRetake').click();
        }
    } catch (err) {
        Swal.fire('Error', 'Gagal menghubungi server: ' + err.message, 'error');
        document.getElementById('btnRetake').click();
    }
});

// Ulangi pengambilan
document.getElementById('btnRetake').addEventListener('click', () => {
    document.getElementById('capturePreview').style.display = 'none';
    document.getElementById('btnCapture').style.display = 'block';
    document.getElementById('btnRetake').style.display = 'none';
    document.getElementById('btnCapture').disabled = true;
    currentDescriptor = null;
    faceDetected = false;
    startDetection();
});

// Hapus data wajah
const btnHapus = document.getElementById('btnHapusFace');
if (btnHapus) {
    btnHapus.addEventListener('click', () => {
        Swal.fire({
            title: 'Hapus Data Wajah?',
            text: 'Data wajah siswa ini akan dihapus dan tidak bisa digunakan untuk absensi.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33',
        }).then(async (result) => {
            if (result.isConfirmed) {
                const response = await fetch(DELETE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    body: JSON.stringify({ nisn: NISN }),
                });
                const data = await response.json();
                if (data.status === 'success') {
                    Swal.fire('Dihapus!', data.message, 'success').then(() => window.location.reload());
                } else {
                    Swal.fire('Gagal', data.message, 'error');
                }
            }
        });
    });
}

// Mulai load model saat halaman siap
loadModels();
</script>
@endpush
@endsection
