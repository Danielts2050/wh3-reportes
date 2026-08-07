@extends('layouts.app')
@section('page-title', 'Materiales 911')
@section('page-description', 'Captura y análisis de despachos de materiales con KPIs operativos.')

@section('content')

<link rel="stylesheet" href="{{ asset('modules/materiales-911.css') }}">

<div class="card corporate-card">
    <div class="card-header" style="background:linear-gradient(135deg,var(--purple-700),var(--purple-500));color:#fff;">
        <i class="fa-solid fa-bell-concierge"></i> Materiales 911
    </div>
    <div class="card-body">

        <div class="helper-card mb-4">
            <strong>Formato esperado (copiar desde Excel):</strong>
            <div class="mt-2 text-muted">
                <code>Material | Fecha | Cantidad | Paletas | Prioridad | Planificado | Vía | Solicitante | Status</code>
            </div>
            <div class="mt-2 small text-muted">
                Si la primera fila pega el encabezado, se detecta y se omite automáticamente.
                Fechas válidas: <code>01/08/2026</code> o <code>04.08.2026</code>. Status <code>Status Q</code> = En proceso de calidad.
            </div>
        </div>

        <form method="POST" action="/materiales-911/procesar" id="form-materiales-911">
            @csrf

            <div class="mb-3">
                <label class="form-label">Datos desde Excel</label>
                <textarea class="form-control font-monospace" rows="10" name="datos" placeholder="Material&#9;Fecha&#9;Cantidad&#9;Paletas&#9;Prioridad&#9;Planificado&#9;Vía&#9;Solicitante&#9;Status">{{ old('datos', $datos ?? '') }}</textarea>
            </div>

            <div class="action-row">
                <button class="btn btn-success" id="btnProcesar">
                    <i class="fa-solid fa-gears"></i> Procesar Reporte
                </button>
            </div>
        </form>

    </div>
</div>

@if($resultado)
    @include('materiales-911.dashboard', ['resultado' => $resultado, 'datos' => $datos])
@endif

@endsection

@section('scripts')
<script>
document.getElementById('form-materiales-911')?.addEventListener('submit', function() {
    if (typeof notifyInfo === 'function') {
        notifyInfo('Procesando Materiales 911...');
    }
});
</script>
@endsection