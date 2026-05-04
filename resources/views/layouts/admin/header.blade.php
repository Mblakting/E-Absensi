      <header class="navbar navbar-expand-md navbar-light d-none d-lg-flex d-print-none">
        <div class="container-xl">
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="navbar-nav flex-row order-md-last">
            <div class="d-none d-md-flex align-items-center me-3">
              {{-- Live Clock --}}
              <span id="live-clock" class="text-muted me-3" style="font-size:0.85rem;font-weight:500;"></span>

              {{-- Dark/Light Mode Toggle --}}
              <a href="?theme=dark" class="nav-link px-0 hide-theme-dark me-1" title="Mode Gelap" data-bs-toggle="tooltip" data-bs-placement="bottom">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" /></svg>
              </a>
              <a href="?theme=light" class="nav-link px-0 hide-theme-light me-1" title="Mode Terang" data-bs-toggle="tooltip" data-bs-placement="bottom">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" /></svg>
              </a>
            </div>

            {{-- User Dropdown --}}
            <div class="nav-item dropdown">
              <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                <span class="avatar avatar-sm bg-primary text-white" style="font-weight:700;font-size:1rem;">
                  {{ strtoupper(substr(Auth::guard('user')->user()->name ?? 'A', 0, 1)) }}
                </span>
                <div class="d-none d-xl-block ps-2">
                  <div style="font-weight:600;">{{ Auth::guard('user')->user()->name ?? 'Admin' }}</div>
                  <div class="mt-1 small text-muted">Administrator</div>
                </div>
              </a>
              <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <div class="dropdown-header">
                  <strong>{{ Auth::guard('user')->user()->name ?? 'Admin' }}</strong>
                  <div class="small text-muted">{{ Auth::guard('user')->user()->email ?? '' }}</div>
                </div>
                <div class="dropdown-divider"></div>
                <a href="/panel/dashboardadmin" class="dropdown-item">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /></svg>
                  Dashboard
                </a>
                <div class="dropdown-divider"></div>
                <a href="{{ route('proseslogoutadmin') }}" class="dropdown-item text-danger"
                   onclick="return confirm('Yakin ingin logout?')">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /><path d="M9 12h12l-3 -3" /><path d="M18 15l3 -3" /></svg>
                  Logout
                </a>
              </div>
            </div>
          </div>

          <div class="collapse navbar-collapse" id="navbar-menu">
            <div>
              <form action="/siswa" method="get" autocomplete="off" novalidate>
                <div class="input-icon">
                  <span class="input-icon-addon">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                  </span>
                  <input type="text" name="nama_siswa" class="form-control" placeholder="Cari siswa…" aria-label="Search">
                </div>
              </form>
            </div>
          </div>
        </div>
      </header>

      <script>
        // Live clock di header
        function updateClock() {
          var now = new Date();
          var days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
          var months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
          var h = String(now.getHours()).padStart(2,'0');
          var m = String(now.getMinutes()).padStart(2,'0');
          var s = String(now.getSeconds()).padStart(2,'0');
          var el = document.getElementById('live-clock');
          if (el) {
            el.textContent = days[now.getDay()] + ', ' + now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear() + '  |  ' + h + ':' + m + ':' + s;
          }
        }
        updateClock();
        setInterval(updateClock, 1000);
      </script>
