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

<form method="POST" action="/planificador/procesar" enctype="multipart/form-data">
    @csrf
    <div class="row plan-form-card">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fa-solid fa-file-import"></i> Plan del Cliente
                </div>
                <div class="card-body">
                    <p class="form-text">Pega los datos (tab-separados): <strong>Material</strong> (TAB) <strong>Cantidad</strong></p>
                    <textarea class="form-control" rows="12" name="plan_cliente"
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
                    <p class="form-text">Sube un archivo Excel (.xlsx, .xls, .csv) con los datos de inventario. Debe tener columnas como: <strong>Material</strong>, <strong>Total Qty</strong>, <strong>Qty/Pallet</strong>, <strong>Total Pallets</strong>, <strong>Blocked</strong>.</p>
                    <input class="form-control" type="file" name="stock" accept=".xlsx,.xls,.csv" style="padding:8px 12px;height:auto;" />
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-4 mb-4">
        <button class="btn btn-primary btn-lg" type="submit">
            <i class="fa-solid fa-play"></i> Procesar
        </button>
    </div>
</form>

@endsection

@section('scripts')
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const plan = this.querySelector('textarea[name="plan_cliente"]').value.trim();
    const stock = this.querySelector('input[name="stock"]').files[0];
    if (!plan) { alert('Ingresa el Plan del Cliente.'); e.preventDefault(); return; }
    if (!stock) { alert('Selecciona un archivo de Stock/Inventario.'); e.preventDefault(); return; }
});
</script>
@endsection
