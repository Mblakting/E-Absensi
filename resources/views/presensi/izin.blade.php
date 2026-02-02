@extends('layouts.presensi')
@section('header')
<div class = "appHeader bg-primary text light">
    <div class="left">
        <a href="/dashboard" class="headerButton goBack">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle">Data Izin / Sakit</div>
    <div class="right"></div>
</div>
@endsection

@section('content')
<div class="row" style="margin-top:70px">
    <div class="col">
        @if($dataizin->count() > 0)
            <table class="table table-hover table-bordered">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>Tanggal Izin</th>
                        <th>Status</th>
                        <th>Alasan</th>
                        <th>Status Persetujuan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dataizin as $izin)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($izin->tgl_izin)->format('d-m-Y') }}</td>
                            <td>
                                @if($izin->status == 'i')
                                    <span class="badge badge-info">Izin</span>
                                @elseif($izin->status == 's')
                                    <span class="badge badge-warning">Sakit</span>
                                @endif
                            </td>
                            <td>{{ $izin->keterangan }}</td>
                            {{-- BARIS BARU: Tambah data Status Persetujuan --}}
                            <td>
                                @if($izin->status_approved == 0)
                                    <span class="badge badge-secondary">Pending</span>
                                @elseif($izin->status_approved == 1)
                                    <span class="badge badge-success">Disetujui</span>
                                @elseif($izin->status_approved == 2)
                                    <span class="badge badge-danger">Ditolak</span>
                                @else
                                    <span class="badge badge-light">Tidak Diketahui</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-info text-center">
                <p><strong>Tidak ada data izin atau sakit.</strong></p>
            </div>
        @endif
    </div>
</div>

<div class="fab-button bottom-right" style="margin-bottom: 70px;">
    <a href="/presensi/buatizin" class="fab">
        <ion-icon name="add-outline"></ion-icon>
    </a>
</div>
@endsection