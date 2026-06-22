<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WH3 Reportes — Iniciar Sesión</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="icon" href="{{ asset('logo_wh3.png') }}">
</head>
<body class="login-page">

    <main class="login-shell">

        <section class="login-brand-panel">
            <div class="login-logo-box">
                <img src="{{ asset('logo_wh3.png') }}" alt="WH3">
            </div>
            <h1>WH3 Reportes</h1>
            <p>Plataforma operativa para reportes, análisis de distribución y control de cumplimiento del plan diario.</p>
        </section>

        <section class="login-card">

            <div class="login-card-header">
                <h2><i class="fa-regular fa-circle-user" style="margin-right:8px;"></i>Iniciar Sesión</h2>
                <p>Accede con tus credenciales corporativas.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" style="border-radius:12px;font-size:13px;">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <div class="input-icon-group">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" name="email" class="form-control"
                               placeholder="usuario@wh3.com"
                               value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <div class="input-icon-group">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" class="form-control"
                               placeholder="••••••••" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember" style="font-size:13px;color:var(--gray-600);">
                            Recordarme
                        </label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="login-link">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>

                <button type="submit" class="login-submit-btn">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Entrar al sistema
                </button>
            </form>

        </section>

    </main>

</body>
</html>
