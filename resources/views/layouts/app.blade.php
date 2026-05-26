<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>WH3 Reportes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">WH3 Reportes</a>

        <div class="navbar-nav">
            <a class="nav-link" href="/reporte">Reporte</a>
            <a class="nav-link" href="/resumen-paletas">Resumen Paletas</a>
            <a class="nav-link" href="/distribucion-lotes">Distribución Lotes</a>
        </div>
    </div>
</nav>

<main class="container mb-5">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>