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
$foto_in=Storage::url('uploads/absensi/'.$d->foto_in);
$foto_out=Storage::url('uploads/absensi/'.$d->foto_out);
@endphp
    <tr>
        <td>{{$loop->iteration}}</td>
        <td>{{$d->nisn}}</td>
        <td>{{$d->nama_lengkap}}</td>
        <td>{{$d->nama_jurusan}}</td>
        <td>{{$d->jam_in}}</td>
        <td>
            <img src="{{url($foto_in)}}" class="avatar" alt="">
        </td>
        <td>{!! $d->jam_out ? $d->jam_out : '<span class="badge bg-danger">Belum Absen Pulang</span>' !!}</td>
        <td>
            @if($d->jam_out!=null)
            <img src="{{url($foto_in)}}" class="avatar" alt="">
            @else
            <img src="{{ asset('assets/img/kosong.jpg') }}" 
                                                        alt="No Photo" 
                                                        width="50" 
                                                        class="avatar avatar-sm"> 
            @endif
        </td>
        <td>
            @if($d->jam_in > '07:00')
                @php
                    $jamterlambat = selisih('07:00:00', $d->jam_in);
                    $waktu_pecah = explode(':', $jamterlambat);
                    $jam = ltrim($waktu_pecah[0], '0') ?: '0';
                    $menit = ltrim($waktu_pecah[1], '0') ?: '0'; 
                @endphp

                <span class="badge bg-danger">
                    Terlambat:
                    @if($jam > 0)
                        {{$jam}} Jam, 
                    @endif
                    {{$menit}} Menit
                </span>
            @else
                <span class="badge bg-success">
                    Tepat Waktu
                </span>
            @endif
        </td>
        <td>
            <a href="#" class="btn btn-primary tampilkanpeta" id="{{$d->id}}"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-map-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 18.5l-3 -1.5l-6 3v-13l6 -3l6 3l6 -3v7.5" /><path d="M9 4v13" /><path d="M15 7v5.5" /><path d="M21.121 20.121a3 3 0 1 0 -4.242 0c.418 .419 1.125 1.045 2.121 1.879c1.051 -.89 1.759 -1.516 2.121 -1.879z" /><path d="M19 18v.01" /></svg></a>
        </td>
    </tr>

@endforeach

<script>
    $(function(){
        $(".tampilkanpeta").click(function(e){
            // e.preventDefault(); // Tambahkan ini jika ingin mencegah default action (misalnya navigasi)
            var id = $(this).attr("id");
            $.ajax({ // <-- PERBAIKAN: Gunakan kurung kurawal setelah $.ajax
                type:'POST',
                url:'/tampilkanpeta',
                data:{
                    _token:"{{csrf_token()}}", // <-- PERBAIKAN: Tambahkan koma di sini
                    id:id // <-- PERBAIKAN: Ganti '_id' menjadi 'id'
                },
                cache:false,
                success:function(respond){
                    $("#loadmap").html(respond);
                }
            }); // <-- PERBAIKAN: Tambahkan titik koma setelah penutup kurung kurawal
            $("#modal-tampilkanpeta").modal("show");
        });
    })
</script>