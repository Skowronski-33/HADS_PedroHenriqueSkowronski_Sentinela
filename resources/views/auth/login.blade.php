<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - Sentinela</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sentinela.css') }}" rel="stylesheet">
</head>
<body class="d-flex align-items-center" style="min-height: 100vh; background-color: var(--sentinela-navy-900);">
    <div class="container" style="max-width: 400px;">
        <div class="card shadow">
            <div class="card-body p-4">
                <h3 class="text-center mb-1">
                    <i class="bi bi-shield-fill-check" style="color: var(--sentinela-amber-600);"></i>
                    Sentinela
                </h3>
                <p class="text-center text-muted mb-4">Sistema de gerenciamento de desastres</p>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" name="email" id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input">
                        <label for="remember" class="form-check-label">Lembrar de mim</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>
            </div>
        </div>
        <p class="text-center text-muted mt-3 small">Sentinela &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>