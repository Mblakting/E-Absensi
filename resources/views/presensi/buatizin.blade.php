@extends('layouts.presensi')
@section('header')
<div class = "appHeader bg-primary text light">
    <div class="left">
        <a href="/dashboard" class="headerButton goBack">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle">Form Izin</div>
    <div class="right"></div>
</div>
@endsection

@section('content')
<div class="row" style="margin-top: 70px;">
    <div class="col">
        <form method="POST" action="{{ route('presensi.storeizin') }}" id="frmIzin">
            @csrf
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <div class="input-wrapper">
                            <label class="label" for="tgl_izin">Tanggal Izin</label>
                            <input type="date" class="form-control" id="tgl_izin" name="tgl_izin" placeholder="Masukkan Tanggal Izin">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label class="label" for="status">Pilih Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="" disabled selected>Status</option>
                            <option value="i">Izin</option>
                            <option value="s">Sakit</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <div class="input-wrapper">
                            <label class="label" for="keterangan">Alasan</label>
                            <textarea class="form-control" id="keterangan" rows="3" name="keterangan" placeholder="Masukkan Alasan"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('myscripts')
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
  $(document).ready(function(){
    $('#frmIzin').submit(function(e){
      e.preventDefault();
      
      var tanggal = $('#tgl_izin').val();
      var status = $('#status').val();
      // PERBAIKAN: Mengambil nilai dari textarea dengan ID yang sudah diperbaiki (keterangan)
      var alasan = $('#keterangan').val(); 
      // Mengambil URL dari atribut action form
      var submitUrl = $(this).attr('action'); 
      var form = $(this).serialize(); 

      if(!tanggal || !status || !alasan){
        swal("GAGAL!", "Semua form harus diisi!", "error");
        return; 
      }

      $.ajax({
        url: submitUrl, 
        method: 'POST',
        data: form,
        dataType: 'json',
        beforeSend: function(){
          $('button[type="submit"]').prop('disabled', true).text('Loading...');
        },
        success: function(response){
          if(response.status === "success"){
            swal("SUKSES!", response.message, "success").then(() => {
              window.location.href = response.redirect || '/presensi/izin';
            });
          } else {
            swal("GAGAL!", response.message || "Pengajuan Izin Gagal!", "error");
            $('button[type="submit"]').prop('disabled', false).text('Submit');
          }
        },
        error: function(xhr, status, error){
            let msg = "Terjadi kesalahan saat mengajukan izin."; 
            if(xhr.responseJSON && xhr.responseJSON.message) {
            msg = xhr.responseJSON.message;
// BARIS BARU: Menambahkan penanganan status 409
            } else if(xhr.status === 409) { 
            msg = "Anda sudah mengajukan izin di tanggal ini.";
            } else if(xhr.status === 422) {
            msg = "Data tidak lengkap atau tidak valid.";
            } else if(xhr.status === 401) {
            msg = "Terjadi kesalahan pada server.";
            }
          
          swal("GAGAL!", msg, "error");
          $('button[type="submit"]').prop('disabled', false).text('Submit');
          console.error('AJAX Error:', xhr);
        }
      });
    });
  });
</script>
@endpush
