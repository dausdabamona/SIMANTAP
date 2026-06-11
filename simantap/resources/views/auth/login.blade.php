<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SIMANTAP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        {{-- Logo --}}
        <div class="auth-logo">
            <i class="bi bi-shield-check-fill"></i>
        </div>
        <h4 class="text-center fw-bold mb-1">SIMANTAP</h4>
        <p class="text-center text-muted mb-4" style="font-size:.8rem">
            Sistem Informasi Manajemen Bantuan Makan Taruna<br>
            <small class="opacity-75">Politeknik KP Sorong</small>
        </p>

        {{-- Flash status --}}
        @if (session('status'))
            <div class="alert alert-success alert-sm py-2 mb-3" role="alert">
                <i class="bi bi-check-circle me-1"></i>{{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input id="email" type="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required autofocus
                           placeholder="email@poltekkpsorong.ac.id">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input id="password" type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           required placeholder="••••••••">
                    <button class="btn btn-outline-secondary" type="button"
                            onclick="togglePwd()" title="Tampilkan password">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember" style="font-size:.82rem">Ingat saya</label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-decoration-none" style="font-size:.82rem">
                        Lupa password?
                    </a>
                @endif
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
            </button>
        </form>

        <hr class="my-4 opacity-25">
        <p class="text-center text-muted mb-0" style="font-size:.72rem">
            SOP PR/PKU/KU-001/2025 &nbsp;·&nbsp; &copy; {{ date('Y') }} Poltek KP Sorong
        </p>
    </div>
</div>

<script>
function togglePwd() {
    const f = document.getElementById('password');
    const i = document.getElementById('eyeIcon');
    if (f.type === 'password') { f.type = 'text'; i.className = 'bi bi-eye-slash'; }
    else { f.type = 'password'; i.className = 'bi bi-eye'; }
}
</script>
</body>
</html>
