<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login | WH3 Reportes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <link rel="icon" href="{{ asset('logo_wh3.png') }}">
</head>

<body class="login-page">

    <main class="login-shell">

        <section class="login-brand-panel">

            <div class="login-logo-box">
                <img src="{{ asset('logo_wh3.png') }}" alt="WH3 Logo">
            </div>

            <h1>
                WH3 Reportes
            </h1>

            <p>
                Plataforma operativa para reportes, horas extras y consolidación de información.
            </p>

        </section>


        <section class="login-card">

            <div class="login-card-header">

                <h2>
                    Iniciar Sesión
                </h2>

                <p>
                    Accede con tus credenciales corporativas.
                </p>

            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    Credenciales inválidas. Verifica tu correo y contraseña.
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">

                @csrf

                <div class="mb-3">

                    <label class="form-label fw-bold">
                        Correo electrónico
                    </label>

                    <div class="input-icon-group">

                        <i class="fa-solid fa-envelope"></i>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="usuario@wh3.com"
                        >

                    </div>

                </div>


                <div class="mb-3">

                    <label class="form-label fw-bold">
                        Contraseña
                    </label>

                    <div class="input-icon-group">

                        <i class="fa-solid fa-lock"></i>

                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                        >

                    </div>

                </div>


                <div class="d-flex justify-content-between align-items-center mb-4">

                    <label class="form-check-label d-flex align-items-center gap-2">

                        <input
                            type="checkbox"
                            name="remember"
                            class="form-check-input"
                        >

                        Recordarme

                    </label>

                    @if (Route::has('password.request'))
                        <a
                            href="{{ route('password.request') }}"
                            class="login-link"
                        >
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif

                </div>


                <button class="btn btn-primary w-100 login-submit-btn">

                    <i class="fa-solid fa-right-to-bracket"></i>

                    Entrar al sistema

                </button>

            </form>

        </section>

    </main>

</body>
</html>