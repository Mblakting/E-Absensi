        <footer class="footer footer-transparent d-print-none">
          <div class="container-xl">
            <div class="row text-center align-items-center flex-row-reverse">
              <div class="col-lg-auto ms-lg-auto">
                <ul class="list-inline list-inline-dots mb-0">
                  <li class="list-inline-item">
                    <a href="{{ route('dashboardadmin') }}" class="link-secondary">Dashboard</a>
                  </li>
                  <li class="list-inline-item">
                    <a href="{{ route('siswa.index') }}" class="link-secondary">Data Siswa</a>
                  </li>
                  <li class="list-inline-item">
                    <a href="{{ route('presensi.monitoring') }}" class="link-secondary">Monitoring</a>
                  </li>
                </ul>
              </div>
              <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                <ul class="list-inline list-inline-dots mb-0">
                  <li class="list-inline-item">
                    &copy; {{ date('Y') }} <strong>{{ config('app.name', 'E-Presensi') }}</strong>. Sistem Presensi Digital.
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </footer>
