@extends('layouts.admin.tabler')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-calendar-event me-2"></i> Data Izin atau Sakit Siswa
                </h2>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter text-nowrap table-bordered" id="izinSakitTable">
                                <thead>
                                    <tr>
                                        <th class="w-1">No</th>
                                        <th>NISN</th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th>Tanggal Izin</th>
                                        <th>Jenis Izin</th>
                                        <th>Keterangan</th>
                                        <th>Status Approve</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($izin_sakit as $d)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        {{-- Kolom NISN (menggunakan data dari $d->nik atau $d->nisn jika ada) --}}
                                        <td>{{ $d->nik ?? $d->nisn }}</td>
                                        {{-- Kolom nama_lengkap --}}
                                        <td>{{ $d->nama_lengkap }}</td>
                                        {{-- Kolom Kelas (menggunakan $d->kelas sesuai tabel di DB) --}}
                                        <td>{{ $d->kelas ?? '-' }}</td>
                                        {{-- Kolom tanggal izin dengan format Indonesia --}}
                                        <td>
                                            {{ date('d-m-Y', strtotime($d->tgl_izin)) }}
                                        </td>
                                        {{-- Logika untuk Jenis Izin --}}
                                        <td>
                                            @if ($d->status == 'i')
                                                <span class="badge bg-info-lt">Izin</span>
                                            @elseif ($d->status == 's')
                                                <span class="badge bg-warning-lt">Sakit</span>
                                            @else
                                                <span class="badge bg-dark-lt">Lainnya</span>
                                            @endif
                                        </td>
                                        {{-- Kolom Keterangan --}}
                                        <td>{{ Str::limit($d->keterangan, 30) }}</td>
                                        {{-- Logika untuk Status Approve (DIUBAH KE $d->status_approved) --}}
                                        <td>
                                            @if ($d->status_approved == 1)
                                                <span class="badge bg-success">Disetujui</span>
                                            @elseif ($d->status_approved == 2)
                                                <span class="badge bg-danger">Ditolak</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </td>
                                        {{-- Kolom Aksi --}}
                                        <td>
                                            <div class="btn-list flex-nowrap">
                                                @if ($d->status_approved == 0)
                                                    {{-- Tombol untuk menampilkan modal persetujuan --}}
                                                    <a href="#" class="btn btn-sm btn-primary"
                                                        data-id-izin-sakit="{{ $d->id }}" data-bs-toggle="modal"
                                                        data-bs-target="#modal-izin-sakit">
                                                        <i class="ti ti-check me-1"></i> Proses
                                                    </a>
                                                @else
                                                    {{-- Tombol untuk membatalkan persetujuan --}}
                                                    <a href="/presensi/batalkan-izin-sakit/{{ $d->id }}" class="btn btn-sm btn-danger btn-batalkan-izin">
                                                        <i class="ti ti-x me-1"></i> Batalkan
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center">Tidak ada data izin atau sakit.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Persetujuan Izin/Sakit --}}
<div class="modal modal-blur fade" id="modal-izin-sakit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-clipboard-check me-2"></i> Persetujuan Izin/Sakit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {{-- Action form approval tidak diubah karena diasumsikan Controller menerima 'status_approve' dari form --}}
            <form action="/presensi/approve-izin-sakit" method="POST" id="formApproveIzinSakit">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="id_izin_sakit_form" name="id_izin_sakit_form">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label class="form-label">Pilih Status Persetujuan</label>
                                <select name="status_approve" id="status_approve" class="form-select" required>
                                    <option value="">Pilih Status</option>
                                    <option value="1">✅ Disetujui</option>
                                    <option value="2">❌ Ditolak</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary ms-auto">
                        <i class="ti ti-send me-1"></i> Proses Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('myscript')
<script>
    // Inisialisasi jQuery (asumsi jQuery sudah dimuat)
    $(function() {
        // 1. Mengisi ID ke dalam Modal saat tombol 'Proses' diklik
        $(document).on('click', '[data-id-izin-sakit]', function(e) {
            e.preventDefault();
            // Ambil ID dari custom attribute 'data-id-izin-sakit'
            var id_izin_sakit = $(this).data("id-izin-sakit");
            // Masukkan ID tersebut ke dalam hidden input di form modal
            $("#id_izin_sakit_form").val(id_izin_sakit);
        });

        // 2. Konfirmasi Pembatalan Izin/Sakit (Improvement UX)
        $(document).on('click', '.btn-batalkan-izin', function(e) {
            var href = $(this).attr('href');
            if (!confirm('Apakah Anda yakin ingin membatalkan status izin/sakit ini?')) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush