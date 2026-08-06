@extends('layouts.app')
@section('page-title', 'KPI Cumplimiento del Plan')
@section('page-description', 'Análisis de cumplimiento semanal y diario, clasificación KPI e impacto de inventario.')

@section('content')

<link rel="stylesheet" href="{{ asset('modules/kpi-cumplimiento-plan.css') }}">

<div class="card corporate-card">
    <div class="card-header" style="background:linear-gradient(135deg,var(--purple-700),var(--purple-500));color:#fff;">
        <i class="fa-solid fa-bullseye"></i> Reporte KPI – Cumplimiento del Plan
    </div>
    <div class="card-body">

        <div class="helper-card mb-4">
            <strong>Formato esperado (copiar desde Excel):</strong>
            <div class="mt-2 text-muted">
                <code>Material | Descripción | Fecha | Cant. Requerida | Cant. Entregada | Pendiente | Status</code>
            </div>
            <div class="mt-2 small text-muted">
                La columna <strong>Fecha</strong> se usa para agrupar los KPIs por semana y por día.
                Ejemplo válido: <code>martes, 4 de agosto de 2026</code>
            </div>
            <div class="mt-2 small text-muted">
                <strong>Status:</strong> 0 = Fuera de inventario, 1 = Agotado, vacío = Sin inconveniente
            </div>
        </div>

        <form method="POST" action="/kpi-cumplimiento-plan/procesar" id="form-kpi">
            @csrf

            <div class="mb-3">
                <label class="form-label">Datos desde Excel</label>
                <textarea class="form-control font-monospace" rows="10" name="datos" placeholder="Material&#9;Descripción&#9;Fecha&#9;Cant. Requerida&#9;Cant. Entregada&#9;Pendiente&#9;Status">{{ old('datos', $datos ?? '') }}</textarea>
            </div>

            <div class="action-row">
                <button class="btn btn-success" id="btnProcesar">
                    <i class="fa-solid fa-gears"></i> Procesar Reporte
                </button>
            </div>
        </form>

    </div>
</div>

@endsection

@section('scripts')
<script>
document.getElementById('form-kpi')?.addEventListener('submit', function() {
    if (typeof notifyInfo === 'function') {
        notifyInfo('Procesando reporte KPI...');
    }
});
</script>
@endsection
