    <!-- App Bottom Menu -->
    <div class="appBottomMenu">
        <a href="{{ route('dashboard') }}" class="item{{ request()->routeIs('dashboard') ? ' active' : '' }}">
            <div class="col">
                <ion-icon name="{{ request()->routeIs('dashboard') ? 'home' : 'home-outline' }}"></ion-icon>
                <strong>Home</strong>
            </div>
        </a>
        <a href="{{ route('presensi.histori') }}" class="item{{ request()->routeIs('presensi.histori') ? ' active' : '' }}">
            <div class="col">
                <ion-icon name="{{ request()->routeIs('presensi.histori') ? 'document-text' : 'document-text-outline' }}" aria-label="Riwayat Presensi"></ion-icon>
                <strong>Riwayat</strong>
            </div>
        </a>
        <a href="{{ route('presensi.face') }}" class="item">
            <div class="col">
                <div class="action-button large">
                    <ion-icon name="scan" aria-label="Absen Wajah"></ion-icon>
                </div>
            </div>
        </a>
        <a href="{{ route('presensi.izin') }}" class="item{{ request()->routeIs('presensi.izin', 'presensi.buatizin') ? ' active' : '' }}">
            <div class="col">
                <ion-icon name="{{ request()->routeIs('presensi.izin', 'presensi.buatizin') ? 'calendar' : 'calendar-outline' }}" aria-label="Pengajuan Izin"></ion-icon>
                <strong>Izin</strong>
            </div>
        </a>
        <a href="{{ route('presensi.editprofile') }}" class="item{{ request()->routeIs('presensi.editprofile') ? ' active' : '' }}">
            <div class="col">
                <ion-icon name="{{ request()->routeIs('presensi.editprofile') ? 'person' : 'person-outline' }}"></ion-icon>
                <strong>Profil</strong>
            </div>
        </a>
    </div>
    <!-- * App Bottom Menu -->
