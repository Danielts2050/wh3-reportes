@extends('layouts.app')
@section('page-title', 'KPI Cumplimiento del Plan')
@section('page-description', 'Análisis de cumplimiento diario, clasificación KPI y capacidad operativa.')

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
                <strong>Status:</strong> 0 = Fuera de inventario, 1 = Agotado, vacío = Sin inconveniente
            </div>
        </div>

        <form method="POST" action="/kpi-cumplimiento-plan/procesar" id="form-kpi">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Fecha del Plan</label>
                    <input type="date" name="fecha_plan" class="form-control" required value="{{ old('fecha_plan', date('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contenedores Completados</label>
                    <input type="number" name="contenedores_completados" class="form-control" min="0" max="8" required value="{{ old('contenedores_completados', '') }}" id="contenedores_input">
                    <small class="form-text">Objetivo diario: 8 contenedores</small>
                </div>
                <div class="col-md-4" id="observaciones_wrapper" style="display:none;">
                    <label class="form-label">Observaciones / Motivo</label>
                    <textarea name="observaciones" class="form-control" rows="2" placeholder="Motivo por el cual no se completaron los 8 contenedores...">{{ old('observaciones', '') }}</textarea>
                </div>
            </div>

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
document.getElementById('contenedores_input')?.addEventListener('input', function() {
    const wrapper = document.getElementById('observaciones_wrapper');
    if (parseInt(this.value) < 8 && this.value !== '') {
        wrapper.style.display = 'block';
    } else {
        wrapper.style.display = 'none';
    }
});

document.getElementById('form-kpi')?.addEventListener('submit', function() {
    if (typeof notifyInfo === 'function') {
        notifyInfo('Procesando reporte KPI...');
    }
});
</script>
@endsection
