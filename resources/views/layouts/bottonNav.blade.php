    <!-- App Bottom Menu -->
    <div class="appBottomMenu">
        <a href="/dashboard" class="item{{ request()->is('dashboard') ? ' active' : '' }}">
            <div class="col">
                <ion-icon name="{{ request()->is('dashboard') ? 'home' : 'home-outline' }}"></ion-icon>
                <strong>Home</strong>
            </div>
        </a>
        <a href="/presensi/histori" class="item{{ request()->is('presensi/histori') ? ' active' : '' }}">
            <div class="col">
                <ion-icon name="{{ request()->is('presensi/histori') ? 'document-text' : 'document-text-outline' }}" aria-label="document text outline"></ion-icon>
                <strong>Riwayat</strong>
            </div>
        </a>
        <a href="/presensi/create" class="item">
            <div class="col">
                <div class="action-button large">
                    <ion-icon name="camera" aria-label="absen"></ion-icon>
                </div>
            </div>
        </a>
        <a href="/presensi/izin" class="item{{ request()->is('presensi/izin') || request()->is('presensi/buatizin') ? ' active' : '' }}">
            <div class="col">
                <ion-icon name="{{ request()->is('presensi/izin') || request()->is('presensi/buatizin') ? 'calendar' : 'calendar-outline' }}" aria-label="calendar outline"></ion-icon>
                <strong>Izin</strong>
            </div>
        </a>
        <a href="/presensi/editprofile" class="item{{ request()->is('presensi/editprofile') ? ' active' : '' }}">
            <div class="col">
                <ion-icon name="{{ request()->is('presensi/editprofile') ? 'person' : 'person-outline' }}"></ion-icon>
                <strong>Profil</strong>
            </div>
        </a>
    </div>
    <!-- * App Bottom Menu -->
