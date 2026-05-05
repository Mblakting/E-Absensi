<?php
 function selisih($jam_masuk, $jam_keluar)
        {
            list($h, $m, $s) = explode(":", $jam_masuk);
            $dtAwal = mktime($h, $m, $s, "1", "1", "1");
            list($h, $m, $s) = explode(":", $jam_keluar);
            $dtAkhir = mktime($h, $m, $s, "1", "1", "1");
            $dtSelisih = $dtAkhir - $dtAwal;
            $totalmenit = $dtSelisih / 60;
            $jam = explode(".", $totalmenit / 60);
            $sisamenit = ($totalmenit / 60) - $jam[0];
            $sisamenit2 = $sisamenit * 60;
            $jml_jam = $jam[0];
            return $jml_jam . ":" . round($sisamenit2);
        }
?>
@foreach($presensi as $d)
@php
$foto_in  = $d->foto_in  ? Storage::url('uploads/absensi/'.$d->foto_in)  : null;
$foto_out = $d->foto_out ? Storage::url('uploads/absensi/'.$d->foto_out) : null;
@endphp
    <tr data-id="{{ $d->id }}">
        <td>{{$loop->iteration}}</td>
        <td>
            <div class="fw-semibold">{{$d->nama_lengkap}}</div>
            <div class="text-muted small">{{$d->nisn}}</div>
        </td>
        <td>{{$d->nama_jurusan}}</td>
        <td class="text-center">
            <span class="badge {{ $d->jam_in > '07:00:00' ? 'bg-danger' : 'bg-success' }}">{{$d->jam_in}}</span>
        </td>
        <td class="text-center">
            @if($foto_in)
                <img src="{{$foto_in}}" alt="Foto Masuk" style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
            @else
                <img src="{{ asset('assets/img/kosong.jpg') }}" style="width:50px;border-radius:8px;">
            @endif
        </td>
        <td class="text-center">{!! $d->jam_out ? '<span class="badge bg-secondary">'.$d->jam_out.'</span>' : '<span class="badge bg-danger">Belum Pulang</span>' !!}</td>
        <td class="text-center">
            @if($d->jam_out && $foto_out)
                <img src="{{$foto_out}}" alt="Foto Pulang" style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
            @else
                <img src="{{ asset('assets/img/kosong.jpg') }}" style="width:50px;border-radius:8px;">
            @endif
        </td>
        <td class="text-center">
            @if($d->jam_in > '07:00')
                @php
                    $jamterlambat = selisih('07:00:00', $d->jam_in);
                    $waktu_pecah = explode(':', $jamterlambat);
                    $jam = ltrim($waktu_pecah[0], '0') ?: '0';
                    $menit = ltrim($waktu_pecah[1], '0') ?: '0';
                @endphp
                <span class="badge bg-danger badge-terlambat">
                    Terlambat @if($jam > 0){{$jam}}j @endif{{$menit}}m
                </span>
            @else
                <span class="badge bg-success">Tepat Waktu</span>
            @endif
        </td>
        <td class="text-center">
            <a href="#" class="btn btn-sm btn-outline-primary tampilkanpeta" data-id="{{$d->id}}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 18.5l-3 -1.5l-6 3v-13l6 -3l6 3l6 -3v7.5" /><path d="M9 4v13" /><path d="M15 7v5.5" /><path d="M21.121 20.121a3 3 0 1 0 -4.242 0c.418 .419 1.125 1.045 2.121 1.879c1.051 -.89 1.759 -1.516 2.121 -1.879z" /><path d="M19 18v.01" /></svg>
            </a>
        </td>
    </tr>
@endforeach