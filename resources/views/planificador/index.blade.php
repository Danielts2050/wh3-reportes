@extends('layouts.app')
@section('page-title', 'Planificador de Carga')
@section('page-description', 'Cruza el plan del cliente con el inventario y planifica contenedores vía drag & drop')

@section('content')
<link rel="stylesheet" href="{{ asset('modules/planificador.css') }}">

<div class="kpi-toolbar">
    <div class="kpi-toolbar-title">
        <i class="fa-solid fa-truck-loading"></i> Planificador de Carga
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fa-solid fa-file-import"></i> Plan del Cliente
            </div>
            <div class="card-body">
                <p class="text-muted small">Pega los datos (tab-separados): <strong>Material</strong> (TAB) <strong>Cantidad</strong></p>
                <textarea class="form-control font-monospace" rows="12" name="plan_cliente"
                    placeholder="PBD3006P0101&#9;4,800.00&#10;PCDD862P0101&#9;750&#10;PCDE443P0101&#9;600">{{ old('plan_cliente') }}</textarea>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fa-solid fa-warehouse"></i> Stock / Inventario
            </div>
            <div class="card-body">
                <p class="text-muted small">Pega los datos (tab-separados): <strong>Material</strong> (TAB) <strong>Total Qty</strong> (TAB) <strong>Qty/Pallet</strong> (TAB) <strong>Total Pallets</strong> (TAB) <strong>Blocked</strong></p>
                <textarea class="form-control font-monospace" rows="12" name="stock"
                    placeholder="FPS053200001&#9;2,870.000&#9;5,040.0000&#9;0.569&#9;2,870.000">{{ old('stock') }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="text-center mt-3">
    <button class="btn btn-primary btn-lg" id="btn-procesar" onclick="procesarPlan()">
        <i class="fa-solid fa-play"></i> Procesar
    </button>
</div>

<form method="POST" action="/planificador/procesar" id="form-procesar">
    @csrf
    <input type="hidden" name="plan_cliente" id="hf-plan">
    <input type="hidden" name="stock" id="hf-stock">
</form>

@endsection

@section('scripts')
<script>
function procesarPlan() {
    const plan = document.querySelector('textarea[name="plan_cliente"]').value.trim();
    const stock = document.querySelector('textarea[name="stock"]').value.trim();

    if (!plan) { alert('Ingresa el Plan del Cliente.'); return; }
    if (!stock) { alert('Ingresa el Stock/Inventario.'); return; }

    document.getElementById('hf-plan').value = plan;
    document.getElementById('hf-stock').value = stock;
    document.getElementById('form-procesar').submit();
}
</script>
@endsection
