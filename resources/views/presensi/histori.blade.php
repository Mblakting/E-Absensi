@extends('layouts.presensi')
@section('header')
<div class = "appHeader bg-primary text light">
    <div class="left">
        <a href="/dashboard" class="headerButton goBack">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle">Histori Presensi</div>
    <div class="right"></div>
</div>
@endsection

@section('content')
<div class="row" style="margin-top:70px">
    <div class="col" style="margin-left:10px; margin-right:10px">
        <div class="row">
            <div class="form-group">
                <select name="bulan" id="bulan" class="form-control">
                    <option value="">Bulan</option>
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{date("m")==$i ? 'selected' :''}}>{{$namabulan[$i]}}</option>
                    @endfor
                </select>
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                <select name="tahun" id="tahun" class="form-control">
                    <option value="">Tahun</option>
                    @php
                    $tahunmulai = 2023;
                    $tahunsekarang = date('Y');
                    @endphp
                    @for ($tahun = $tahunmulai; $tahun <= $tahunsekarang; $tahun++) 
                        <option value="{{$tahun}}" {{date("Y")==$tahun ? 'selected' :''}}>{{$tahun}}</option>
                    @endfor
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    <button class="btn btn-primary btn-block" id="getdata">
                        <ion-icon name="search-outline"></ion-icon>Pilih
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" style="margin-top:20px">
    <div class="col" id="showhistori"></div>
</div>

@endsection

@push('myscripts')
<script>
    $(function(){
        $("#getdata").click(function(e){
            e.preventDefault();
            var bulan = $('#bulan').val();
            var tahun = $('#tahun').val();

            if (!bulan || !tahun) {
                alert('Silakan pilih bulan dan tahun terlebih dahulu');
                return;
            }

            $.ajax({
                type: 'POST',
                url: '{{ route("presensi.gethistori") }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    bulan: bulan,
                    tahun: tahun
                },
                cache: false,
                beforeSend: function(){
                    $('#getdata').prop('disabled', true).text('Loading...');
                },
                success: function(respond){
                    $("#showhistori").html(respond);
                    $('#getdata').prop('disabled', false).html('<ion-icon name="search-outline"></ion-icon>Pilih');
                },
                error: function(xhr, status, err){
                    console.error('AJAX error:', status, err);
                    alert('Terjadi kesalahan saat mengambil histori. Cek console untuk detail.');
                    $('#getdata').prop('disabled', false).html('<ion-icon name="search-outline"></ion-icon>Pilih');
                }
            });
        });
    });
</script>
@endpush