@extends('layouts.presensi')
@section('header')
    <!-- App Header -->
    <div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="javascript:;" class="headerButton goBack">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">Absensi</div>
        <div class="right"></div>
    </div>
    <!-- * App Header -->

<style>
    .webcam-capture, .webcam-capture video{
        display: inline-block;
        width: 100% !important;
        margin: auto;
        height: auto !important;
        border-radius: 15px;
    }

    #map { height: 300px; }
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection

@section('content')
    <!-- App Capsule -->
    <div id="row" style="margin-top:14px;">
        <div class="col">
            <input type="hidden" id="lokasi">
            <div class="webcam-capture"></div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            @if ($cek > 0)
                <button id="takeabsen" class="btn btn-danger btn-block">
                    <ion-icon name="camera-outline"></ion-icon>Absen Pulang
                </button>
            @else
                <button id="takeabsen" class="btn btn-primary btn-block">
                   <ion-icon name="camera-outline"></ion-icon>Absen Masuk
                </button> 
            @endif
        </div>
    </div>
    <div class="row mt-2">
        <div class="col">
            <div id="map"></div>
        </div>
    </div>
    <!-- * App Capsule -->
@endsection

@push('myscripts')
<script>
    Webcam.set({
        width: 480,
        height: 360,
        image_format: 'jpeg',
        jpeg_quality: 85,
        force_flash: false,
        flip_horiz: true
    });

    Webcam.attach('.webcam-capture');

    // Ambil lokasi GPS
    var lokasiInput = document.getElementById('lokasi');
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(successCallback, errorCallback, {
            enableHighAccuracy: true,
            timeout: 10000
        });
    } else {
        alert('Browser tidak mendukung geolocation');
    }

    function successCallback(position) {
        lokasiInput.value = position.coords.latitude + ',' + position.coords.longitude;
        var lat = position.coords.latitude;
        var lng = position.coords.longitude;
        var map = L.map('map').setView([lat, lng], 16);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
        L.marker([lat, lng]).addTo(map);
        // Lingkaran area sekolah
        L.circle([-7.671325, 110.589607], {
            color: 'green',
            fillColor: 'green',
            fillOpacity: 0.3,
            radius: 200
        }).addTo(map);
    }

    function errorCallback(error) {
        alert('Gagal mendapatkan lokasi: ' + error.message);
    }

    $('#takeabsen').click(function(e) {
        e.preventDefault();
        var lokasiVal = $('#lokasi').val();
        if (!lokasiVal) {
            swal('Perhatian', 'Lokasi GPS belum tersedia. Tunggu sebentar atau aktifkan GPS.', 'warning');
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses...');

        Webcam.snap(function(dataUri) {
            $.ajax({
                type: 'POST',
                url: '{{ route("presensi.store") }}',
                data: JSON.stringify({
                    _token: '{{ csrf_token() }}',
                    image: dataUri,
                    lokasi: lokasiVal
                }),
                contentType: 'application/json',
                processData: false,
                cache: false,
                success: function(respond) {
                    if (respond.status === 'success') {
                        swal('SUKSES!', respond.message, 'success');
                        setTimeout(function() {
                            window.location.href = '/dashboard';
                        }, 2000);
                    } else {
                        swal('GAGAL!', respond.message || 'Absensi gagal!', 'error');
                        btn.prop('disabled', false).html('<ion-icon name="camera-outline"></ion-icon> Coba Lagi');
                    }
                },
                error: function(xhr) {
                    var msg = 'Terjadi kesalahan server';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    swal('ERROR!', msg, 'error');
                    btn.prop('disabled', false).html('<ion-icon name="camera-outline"></ion-icon> Coba Lagi');
                }
            });
        });
    });
</script>
@endpush