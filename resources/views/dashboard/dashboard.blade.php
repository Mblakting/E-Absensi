@extends('layouts.presensi')

@section('content')
    <div class="section" id="user-section">
        <div id="user-detail">
            <div class="avatar">
                @if(!empty(Auth::guard('siswa')->user()->foto))
                    @php
                        $path = Storage::url('uploads/foto_siswa/'.Auth::guard('siswa')->user()->foto);
                    @endphp
                    <img src="{{ url($path) }}" alt="avatar" class="imaged w48">
                @else
                    <img src="assets/img/sample/avatar/avatar1.jpg" alt="avatar" class="imaged w48">
                @endif
            </div>
            <div id="user-info">
                <h2 id="user-name">{{Auth::guard('siswa')->user()->nama_lengkap}}</h2>
                <span id="user-role">{{Auth::guard('siswa')->user()->kelas}} : {{Auth::guard('siswa')->user()->nisn}}</span>
            </div>
        </div>
    </div>

    <div class="section" id="menu-section">
        <div class="card">
            <div class="card-body text-center">
                <div class="list-menu">
                    <div class="item-menu text-center">
                        <div class="menu-icon">
                            <a href="" class="green" style="font-size: 40px;">
                                <ion-icon name="person-sharp"></ion-icon>
                            </a>
                        </div>
                        <div class="menu-name">
                            <span class="text-center">Profil</span>
                        </div>
                    </div>
                    <div class="item-menu text-center">
                        <div class="menu-icon">
                            <a href="" class="danger" style="font-size: 40px;">
                                <ion-icon name="calendar-number"></ion-icon>
                            </a>
                        </div>
                        <div class="menu-name">
                            <span class="text-center">Cuti</span>
                        </div>
                    </div>
                    <div class="item-menu text-center">
                        <div class="menu-icon">
                            <a href="" class="warning" style="font-size: 40px;">
                                <ion-icon name="document-text"></ion-icon>
                            </a>
                        </div>
                        <div class="menu-name">
                            <span class="text-center">Histori</span>
                        </div>
                    </div>
                    <div class="item-menu text-center">
                        <div class="menu-icon">
                            <a href="" class="orange" style="font-size: 40px;">
                                <ion-icon name="location"></ion-icon>
                            </a>
                        </div>
                        <div class="menu-name">
                            Lokasi
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="section mt-2" id="presence-section">
        <div class="todaypresence">
            <div class="row">
                <div class="col-6">
                    <div class="card gradasigreen">
                        <div class="card-body">
                            <div class="presencecontent">
                                <div class="iconpresence">
                                    {{-- **Perbaikan untuk Foto Masuk** --}}
                                    @if($presensihariini != null && $presensihariini->foto_in != null)
                                        @php
                                            $path = Storage::url('uploads/absensi/'.$presensihariini->foto_in);
                                        @endphp
                                        <img src="{{ url($path) }}" alt="Foto Masuk" class="imaged w32">
                                    @else
                                        <ion-icon name="camera"></ion-icon>
                                    @endif
                                    {{-- **Akhir Perbaikan Foto Masuk** --}}
                                </div>
                                <div class="presencedetail">
                                    <h4 class="presencetitle">Masuk</h4>
                                    <span>{{$presensihariini != null ? $presensihariini->jam_in : "Belum Absen"}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card gradasired">
                        <div class="card-body">
                            <div class="presencecontent">
                                <div class="iconpresence">
                                    {{-- **Perbaikan untuk Foto Pulang** --}}
                                    @if($presensihariini != null && $presensihariini->foto_out != null)
                                        @php
                                            $path = Storage::url('uploads/absensi/'.$presensihariini->foto_out);
                                        @endphp
                                        <img src="{{ url($path) }}" alt="Foto Pulang" class="imaged w32">
                                    @else
                                        <ion-icon name="camera"></ion-icon>
                                    @endif
                                    {{-- **Akhir Perbaikan Foto Pulang** --}}
                                </div>
                                <div class="presencedetail">
                                    <h4 class="presencetitle">Pulang</h4>
                                    <span>{{$presensihariini != null && $presensihariini->jam_out != null ? $presensihariini->jam_out : "Belum Absen"}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="rekappresensi">
            <h3>Rekap Absen Bulan {{$namabulan[$bulanini]}} Tahun {{$tahunini}}</h3>
            <div class="row">
                <div class="col-3">
                    <div class="card">
                        <div class="card-body text-center" style="padding:20px 0 12px 0;">
                            <span class = "badge bg-danger" style="position:absolute;top:3px;right: 10px;font: size 0.6rem;z-index:999">{{$rekappresensi->jmlhadir}}</span>
                            <h4 class="text-primary">Hadir</h4>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card">
                        <div class="card-body text-center" style="padding:20px 0 12px 0;">
                            <span class = "badge bg-danger" style="position:absolute;top:3px;right: 10px;font: size 0.6rem;z-index:999">{{$rekapizin->jmlizin}}</span>
                            <h4 class="text-primary">Izin</h4>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card">
                        <div class="card-body text-center" style="padding:20px 0 12px 0;">
                            <span class = "badge bg-danger" style="position:absolute;top:3px;right: 10px;font: size 0.6rem;z-index:999">{{$rekapizin->jmlsakit}}</span>
                            <h4 class="text-primary">Sakit</h4>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card">
                        <div class="card-body text-center" style="padding:20px 0 12px 0;">
                            <span class = "badge bg-danger" style="position:absolute;top:3px;right: 10px;font: size 0.6rem;z-index:999">{{$rekappresensi->jmlterlambat}}</span>
                            <h4 class="text-primary">Telat</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="presencetab mt-2">
            <div class="tab-pane fade show active" id="pilled" role="tabpanel">
                <ul class="nav nav-tabs style1" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#home" role="tab">
                            Absen Anda Bulan Ini
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#profile" role="tab">
                            Leaderboard
                        </a>
                    </li>
                </ul>
            </div>
            <div class="tab-content mt-2" style="margin-bottom:100px;">
                <div class="tab-pane fade show active" id="home" role="tabpanel">
                    <ul class="listview image-listview">
                        @foreach($historibulanini as $d)
                        <li>
                            <div class="item">
                                <div class="icon-box bg-primary">
                                    <ion-icon name="finger-print-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    <div>{{ date ("d-m-Y", strtotime($d->tgl_presensi))}}</div>
                                    <div>
                                    <span class="badge {{ $d->jam_in >= '07:00:00' ? 'bg-warning' : 'bg-success' }}">{{$d->jam_in}}</span>
                                    <span class="badge badge-danger">{{$d->jam_out != null ? $d->jam_out : 'Belum Absen'}}</span>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div class="tab-pane fade" id="profile" role="tabpanel">
                    <ul class="listview image-listview">
                        @foreach($leaderboard as $d)
                        <li>
                            <div class="item">
                                {{-- **Koreksi Logika Foto Siswa pada Leaderboard** --}}
                                @if(!empty($d->foto))
                                    @php
                                        // Asumsikan $d->foto berisi nama file foto siswa
                                        $path_lb = Storage::url('uploads/foto_siswa/'.$d->foto);
                                    @endphp
                                    <img src="{{ url($path_lb) }}" alt="image" class="image">
                                @else
                                    <img src="assets/img/sample/avatar/avatar1.jpg" alt="image" class="image">
                                @endif
                                {{-- **Akhir Koreksi Foto Siswa** --}}
                                <div class="in">
                            <div class="in">
                                <div>
                                    <b>{{$d->nama_lengkap}}</b><br>
                                    <small class="text-muted">{{$d->kelas}}</small>
                                </div>
                                <span class="badge {{ $d->jam_in >= '07:00:00' ? 'bg-danger' : 'bg-success' }}">
                                    {{$d->jam_in}}
                                </span>
                            </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection