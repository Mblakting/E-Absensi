<!doctype html>
<html lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#1a73e8">
    <title>E-Presensi — Login</title>
    <link rel="icon" type="image/png" href="{{asset('assets/img/favicon.png')}}" sizes="32x32">
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('assets/img/icon/192x192.png')}}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css')}}">
    <style>
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #aaa;
            z-index: 10;
            background: none;
            border: none;
            padding: 0;
        }
        .password-toggle:hover { color: #555; }
        .input-wrapper { position: relative; }
        .btn-login-animate {
            transition: transform .15s, box-shadow .15s;
        }
        .btn-login-animate:active {
            transform: scale(0.97);
        }
        .login-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 8px;
        }
        .login-brand ion-icon {
            font-size: 2.5rem;
            color: #1a73e8;
        }
    </style>
</head>

<body class="bg-white">

    <!-- loader -->
    <div id="loader">
        <div class="spinner-border text-primary" role="status"></div>
    </div>
    <!-- * loader -->

    <!-- App Capsule -->
    <div id="appCapsule" class="pt-0">
        <div class="login-form mt-1">
            <div class="section">
                <img src="{{asset('assets/img/login/login.jpg')}}" alt="image" class="form-image">
            </div>
            <div class="section mt-1">
                <div class="login-brand">
                    <ion-icon name="finger-print-outline"></ion-icon>
                    <h1 style="margin:0;">E-Presensi</h1>
                </div>
                <h4 class="text-muted text-center">Silahkan Login untuk melanjutkan</h4>
            </div>
            <div class="section mt-1 mb-5">
                @php $messagewarning = Session::get('warning'); @endphp
                @if ($messagewarning)
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <ion-icon name="warning-outline" style="font-size:20px;margin-right:8px;"></ion-icon>
                    {{ $messagewarning }}
                </div>
                @endif

                <form action="/proseslogin" method="POST" id="loginForm">
                    @csrf
                    <div class="form-group boxed">
                        <div class="input-wrapper">
                            <input type="text" name="nisn" class="form-control" id="nisn"
                                   placeholder="Masukkan NISN" autocomplete="off" required>
                            <i class="clear-input">
                                <ion-icon name="close-circle"></ion-icon>
                            </i>
                        </div>
                    </div>

                    <div class="form-group boxed">
                        <div class="input-wrapper">
                            <input type="password" class="form-control" id="password1" name="password"
                                   placeholder="Masukkan Password" required>
                            <button type="button" class="password-toggle" id="togglePassword" tabindex="-1">
                                <ion-icon name="eye-outline" id="eyeIcon"></ion-icon>
                            </button>
                            <i class="clear-input">
                                <ion-icon name="close-circle"></ion-icon>
                            </i>
                        </div>
                    </div>

                    <div class="form-button-group mt-3">
                        <button type="submit" class="btn btn-primary btn-block btn-lg btn-login-animate" id="btnLogin">
                            <ion-icon name="log-in-outline" style="font-size:20px;vertical-align:-4px;margin-right:6px;"></ion-icon>
                            Masuk
                        </button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <small class="text-muted">Login sebagai Admin? <a href="/panel">Klik di sini</a></small>
                </div>
            </div>
        </div>
    </div>
    <!-- * App Capsule -->

    <!-- JS Files -->
    <script src="{{asset('assets/js/lib/jquery-3.4.1.min.js')}}"></script>
    <script src="{{asset('assets/js/lib/popper.min.js')}}"></script>
    <script src="{{asset('assets/js/lib/bootstrap.min.js')}}"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.0.0/dist/ionicons/ionicons.js"></script>
    <script src="{{asset('assets/js/base.js')}}"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            var input = document.getElementById('password1');
            var icon  = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('name', 'eye-off-outline');
            } else {
                input.type = 'password';
                icon.setAttribute('name', 'eye-outline');
            }
        });

        // Loading state saat submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            var btn = document.getElementById('btnLogin');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Memproses...';
        });
    </script>
</body>
</html>
