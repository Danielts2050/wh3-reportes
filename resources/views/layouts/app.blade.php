<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>WH3 Reportes</title>

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    <link rel="icon" href="{{ asset('logo_wh3.png') }}">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

<div class="dashboard-layout">

    <aside class="sidebar">

        <div class="sidebar-header">

            <div class="logo-box">
                <img src="{{ asset('logo_wh3.png') }}" alt="WH3 Logo">
            </div>

            <div>

                <h4>
                    Sistema WH3
                </h4>

                <small>
                    Reportes Inteligentes
                </small>

            </div>

        </div>


        <nav class="sidebar-nav">

            <a
            href="/resumen-paletas"
            class="
            nav-item
            {{ request()->is('resumen-paletas*') ? 'active':'' }}
            "
            >

            <i class="fa-solid fa-boxes-stacked"></i>

            <span>
            Resumen Paletas
            </span>

            </a>



            <a
            href="/distribucion-lotes"
            class="
            nav-item
            {{ request()->is('distribucion-lotes*') ? 'active':'' }}
            "
            >

            <i class="fa-solid fa-table-cells-large"></i>

            <span>
            Distribución Lotes
            </span>

            </a>



            <a
            href="/reporte"
            class="
            nav-item
            {{ request()->is('reporte*') ? 'active':'' }}
            "
            >

            <i class="fa-solid fa-chart-column"></i>

            <span>
            Reporte WH3
            </span>

            </a>

        </nav>

    </aside>


    <main class="content-wrapper">

       <header class="hero-section">

            <h1>
            @yield('page-title', 'Sistema WH3 Reportes')
            </h1>

            <p>
                @yield('page-description', 'Procesamiento operativo y análisis inteligente')
            </p>

        </header>


        <main class="content-area">

            @yield('content')

        </main>

    </main>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@yield('scripts')

</body>
</html>