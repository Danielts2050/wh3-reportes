@extends('layouts.app')
@section('page-title', 'Planificador de Carga — Tablero')
@section('page-description', 'Arrastra los materiales a los contenedores para planificar la carga')

@section('content')
<link rel="stylesheet" href="{{ asset('modules/planificador.css') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Toolbar --}}
<div class="plan-toolbar">
    <div class="plan-toolbar-title">
        <i class="fa-solid fa-truck-loading"></i> Tablero de Carga
    </div>
    <div class="plan-toolbar-actions">
        <button class="btn btn-secondary no-print" id="btn-print">
            <i class="fa-solid fa-print"></i> Imprimir
        </button>
        <button class="btn btn-success no-print" id="export-excel">
            <i class="fa-solid fa-file-excel"></i> Exportar Excel
        </button>
        <a href="/planificador" class="btn btn-outline-secondary no-print">
            <i class="fa-solid fa-arrow-left"></i> Nuevo
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="plan-stats">
    <div class="plan-stat">
        <span class="num" id="stat-materiales">{{ $total_materiales }}</span>
        <span class="lbl">Materiales</span>
    </div>
    <div class="plan-stat">
        <span class="num" id="stat-palets-req">{{ number_format($total_palets_requeridos, 2) }}</span>
        <span class="lbl">Palets Requeridos</span>
    </div>
    <div class="plan-stat">
        <span class="num" id="stat-palets-disp">{{ number_format($total_palets_disponibles, 2) }}</span>
        <span class="lbl">Palets Disponibles</span>
    </div>
    <div class="plan-stat">
        <span class="num" id="stat-contenedores-uso">0/8</span>
        <span class="lbl">Contenedores en uso</span>
    </div>
</div>

{{-- Board --}}
<div class="plan-board">
    {{-- Materiales Panel --}}
    <div class="plan-materiales" id="panel-materiales">
        <div class="plan-materiales-header no-print">
            <h3><i class="fa-solid fa-cubes"></i> Materiales</h3>
            <button class="plan-toggle-agotados" id="toggle-agotados" data-show="false">Mostrar agotados</button>
        </div>
        <input class="plan-busqueda no-print" id="busqueda" type="text" placeholder="Buscar material..." />
        <div id="materiales-list"></div>
    </div>

    {{-- Contenedores --}}
    <div class="plan-contenedores" id="contenedores-grid"></div>
</div>

{{-- Modal --}}
<div class="plan-modal-overlay" id="modal-overlay">
    <div class="plan-modal">
        <h4 id="modal-title">Asignar material</h4>
        <p>¿Cuántas paletas deseas agregar a este contenedor?</p>
        <div class="modal-info" id="modal-info"></div>
        <input class="modal-input" id="modal-input" type="number" min="1" step="1" autofocus />
        <div class="modal-actions">
            <button class="btn btn-outline-secondary" id="modal-cancel">Cancelar</button>
            <button class="btn btn-primary" id="modal-confirm">Asignar</button>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="plan-toast" id="toast">
    <span id="toast-msg"></span>
    <span class="toast-actions" id="toast-actions"></span>
</div>

{{-- Undo Button --}}
<button class="plan-undo-btn no-print" id="undo-btn" disabled title="Deshacer (Ctrl+Z)">
    <i class="fa-solid fa-rotate-left"></i>
</button>
<button class="plan-undo-btn no-print" id="redo-btn" disabled title="Rehacer (Ctrl+Shift+Z)">
    <i class="fa-solid fa-rotate-right"></i>
</button>

@endsection

@section('scripts')
<script>
window.PLAN_MATERIALES = @json($materiales);
</script>
<script src="{{ asset('modules/planificador.js') }}"></script>
@endsection
