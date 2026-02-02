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
        display: inlie-block;
        width: 100% !important;
        margin: auto;
        height: auto !important;
        border-radius: 15px;
    }

    #map { height: 500px; }
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection

@section('content')
    <!-- App Capsule -->
    <div id="row" style="margin-top:70px;">
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
        height: 640,
        image_format: 'jpeg',
        jpeg_quality: 80
    });

    Webcam.attach('.webcam-capture');

    var lokasi = document.getElementById('lokasi');
    if( navigator.geolocation ){
        navigator.geolocation.getCurrentPosition(sussessCallback, errorCallback);
    }
    function sussessCallback(position){
        lokasi.value = position.coords.latitude + ", " + position.coords.longitude;
        var map = L.map('map').setView([position.coords.latitude, position.coords.longitude], 16);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
        var marker = L.marker([position.coords.latitude, position.coords.longitude]).addTo(map);
        var circle = L.circle([-7.671325, 110.589607], {
            color: 'green',
            fillColor: 'green',
            fillOpacity: 0.5,
            radius: 100
        }).addTo(map);
    }
    function errorCallback(error){
        alert(error.message);
    }

    $("#takeabsen").click(function(e){
        Webcam.snap(function(uri) {
            var lokasi = $('#lokasi').val();
            $.ajax({
                type: 'POST',
                url: '/presensi/store',
                data: JSON.stringify({
                    _token: "{{ csrf_token() }}",
                    image: uri,
                    lokasi: lokasi
                }),
                contentType: 'application/json',
                processData: false,
                cache: false,
                success: function(respond){
                    if(respond.status === "success"){
                        swal("SUKSES!", respond.message, "success");
                        setTimeout(() => {
                            window.location.href = "/dashboard";
                        }, 3000);
                    }else{
                        swal("GAGAL!", respond.message || "Absensi Anda Gagal!", "error");
                    }
                },
                error: function(xhr){
                    let msg = "Terjadi kesalahan server";
                    if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    alert(msg);
                }
            });
        });
    });
</script>
@endpush