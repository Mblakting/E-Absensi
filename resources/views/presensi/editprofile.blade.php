@extends('layouts.presensi')
@section('header')
<div class = "appHeader bg-primary text light">
    <div class="left">
        <a href="{{ route('dashboard') }}" class="headerButton goBack">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle">Edit Profile</div>
    <div class="right"></div>
</div>
@endsection

@section('content')
<div class="row" style="margin-top: 14px; margin-bottom: -3rem;">
    <div class="col text-center">
        @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
        @endif
        @if (session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
        @endif
    </div>
</div>
<form action="{{ route('presensi.updateprofile') }}" method="POST" enctype="multipart/form-data" style="margin-top: 4rem;">
    @csrf
    <div class="col">
        <div class="form-group boxed">
            <div class="input-wrapper">
                <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" value="{{ old('nama_lengkap', $siswa->nama_lengkap) }}" name="nama_lengkap" placeholder="Nama Lengkap" autocomplete="off">
                @error('nama_lengkap')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="form-group boxed">
            <div class="input-wrapper">
                <input type="text" class="form-control @error('kelas') is-invalid @enderror" value="{{ old('kelas', $siswa->kelas) }}" name="kelas" placeholder="Kelas" autocomplete="off">
                @error('kelas')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="form-group boxed">
            <div class="input-wrapper">
                <input type="text" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp', $siswa->no_hp) }}" name="no_hp" placeholder="No. HP" autocomplete="off">
                @error('no_hp')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="form-group boxed">
            <div class="input-wrapper">
                <textarea class="form-control @error('alamat') is-invalid @enderror" name="alamat" placeholder="Alamat" rows="3">{{ old('alamat', $siswa->alamat ?? '') }}</textarea>
                @error('alamat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="form-group boxed">
            <div class="input-wrapper">
                <input type="text" class="form-control @error('nama_orang_tua') is-invalid @enderror" value="{{ old('nama_orang_tua', $siswa->nama_orang_tua ?? '') }}" name="nama_orang_tua" placeholder="Nama Orang Tua / Wali" autocomplete="off">
                @error('nama_orang_tua')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="form-group boxed">
            <div class="input-wrapper">
                <input type="password" class="form-control" name="password" placeholder="Password" autocomplete="off">
            </div>
        </div>
        <div class="form-group boxed">
            <div class="input-wrapper">
                <input type="password" class="form-control" name="password_confirmation" placeholder="Konfirmasi Password" autocomplete="off">
            </div>
        </div>
        <div class="custom-file-upload" id="fileUpload1">
            <input type="file" name="foto" id="fileuploadInput" accept=".png, .jpg, .jpeg">
            <label for="fileuploadInput">
                <span>
                    <strong>
                        <ion-icon name="cloud-upload-outline" role="img" class="md hydrated" aria-label="cloud upload outline"></ion-icon>
                        <i>Upload Foto JPG/PNG Maks. 1MB</i>
                    </strong>
                </span>
            </label>
        </div>
        @error('foto')
            <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
        <div class="form-group boxed">
            <div class="input-wrapper">
                <button type="submit" class="btn btn-primary btn-block">
                    <ion-icon name="refresh-outline"></ion-icon>
                    Update
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
