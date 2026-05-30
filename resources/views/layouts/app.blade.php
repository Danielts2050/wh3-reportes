<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>WH3 Reportes</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">

    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <link rel="icon" href="{{ asset('logo_wh3.png') }}">
</head>

<body>

<div class="dashboard-layout">

    <aside class="sidebar">

        <div class="sidebar-header">

            <div class="logo-box">

                <img
                src="{{ asset('logo_wh3.png') }}"
                alt="WH3">

            </div>

            <h3>
                Sistema WH3
            </h3>

            {{-- <small>
                Reportes Inteligentes
            </small> --}}

        </div>


        <nav class="sidebar-nav">

            <a
            href="/resumen-paletas"
            class="nav-item {{ request()->is('resumen-paletas*') ? 'active':'' }}"
            >

                <i class="fa-solid fa-boxes-stacked"></i>

                Resumen Paletas

            </a>


            <a
            href="/distribucion-lotes"
            class="nav-item {{ request()->is('distribucion-lotes*') ? 'active':'' }}"
            >

                <i class="fa-solid fa-table-cells-large"></i>

                Distribución Lotes

            </a>


            <a
            href="#"
            class="nav-item {{ request()->is('reporte*') ? 'active':'' }}"
            >

                <i class="fa-solid fa-chart-column"></i>

                Reporte WH3

            </a>

            <a
                href="/horas-extras"
                class="nav-item {{ request()->is('horas-extras*') ? 'active':'' }}"
                >

                    <i class="fa-solid fa-clock"></i>

                    Horas Extras

            </a>

        </nav>

    </aside>


    <main class="content-wrapper">

        <header class="hero-section">

            <h1>
                @yield('page-title')
            </h1>

            <p>
                @yield('page-description')
            </p>

        </header>


        <section class="content-area">

            @yield('content')

        </section>

    </main>

</div>

@yield('scripts')

</body>
</html>