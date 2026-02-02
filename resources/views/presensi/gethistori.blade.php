@if($histori->count() > 0)
    <table class="table table-hover table-bordered">
        <thead class="bg-primary text-white">
            <tr>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($histori as $h)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($h->tgl_presensi)->format('d-m-Y') }}</td>
                    <td>{{ $h->jam_in ?? '-' }}</td>
                    <td>{{ $h->jam_out ?? '-' }}</td>
                    <td>
                        @if($h->jam_in && $h->jam_out)
                            <span class="badge badge-success">Lengkap</span>
                        @elseif($h->jam_in && !$h->jam_out)
                            <span class="badge badge-warning">Masuk Saja</span>
                        @else
                            <span class="badge badge-danger">Belum Absen</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div class="alert alert-info text-center">
        <p><strong>Tidak ada data histori untuk bulan dan tahun yang dipilih.</strong></p>
    </div>
@endif
