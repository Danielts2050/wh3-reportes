@extends('layouts.app')
@section('page-title', 'Dashboard KPI – Cumplimiento del Plan')
@section('page-description', 'Reporte generado para {{ $fecha_plan }}')

@section('content')

<link rel="stylesheet" href="{{ asset('modules/kpi-cumplimiento-plan.css') }}">

{{-- Toolbar --}}
<div class="kpi-toolbar">
    <div class="kpi-toolbar-title">
        <i class="fa-solid fa-chart-simple"></i> Dashboard KPI
    </div>
    <div class="kpi-toolbar-actions">
        <form method="POST" action="/kpi-cumplimiento-plan/exportar-pdf" id="form-export-pdf">
            @csrf
            <input type="hidden" name="datos" value="{{ $datos }}">
            <input type="hidden" name="fecha_plan" value="{{ $fecha_plan }}">
            <input type="hidden" name="contenedores_completados" value="{{ $contenedores_completados }}">
            <input type="hidden" name="observaciones" value="{{ $observaciones }}">
            <button class="btn btn-danger" id="btnExportarPdf">
                <i class="fa-solid fa-file-pdf"></i> Exportar PDF
            </button>
        </form>
    </div>
</div>

{{-- A) Barra de Resumen Rápido --}}
<div class="kpi-header-bar">
    <div class="kpi-header-item">
        <span class="kpi-header-label"><i class="fa-regular fa-calendar"></i> Fecha del Plan</span>
        <span class="kpi-header-value">{{ \Carbon\Carbon::parse($fecha_plan)->format('d/m/Y') }}</span>
    </div>
    <div class="kpi-header-item">
        <span class="kpi-header-label"><i class="fa-solid fa-boxes-stacked"></i> Cont. Planificados</span>
        <span class="kpi-header-value">{{ $contenedores_planificados }}</span>
    </div>
    <div class="kpi-header-item">
        <span class="kpi-header-label"><i class="fa-solid fa-check-circle"></i> Cont. Completados</span>
        <span class="kpi-header-value">{{ $contenedores_completados }}</span>
    </div>
    <div class="kpi-header-item">
        <span class="kpi-header-label"><i class="fa-solid fa-gauge-high"></i> Capacidad Operativa</span>
        <span class="kpi-header-value kpi-value-{{ $capacidad_operativa < 100 ? 'warning' : 'success' }}">{{ number_format($capacidad_operativa, 2) }}%</span>
    </div>
    <div class="kpi-header-item">
        <span class="kpi-header-label"><i class="fa-solid fa-flag"></i> Estado del Día</span>
        <span class="kpi-badge kpi-badge-{{ $estado_dia === 'Completo' ? 'success' : 'warning' }}">● {{ $estado_dia }}</span>
    </div>
</div>

{{-- B) Tarjetas de Métricas Clave --}}
<div class="kpi-resumen-rapido">
    <div class="kpi-stat-card">
        <div class="stat-icon" style="color:#2e7d32;"><i class="fa-solid fa-list"></i></div>
        <div class="stat-value">{{ $total_items_plan }}</div>
        <div class="stat-label">Total Ítems</div>
        <div class="stat-sub">del Plan</div>
    </div>
    <div class="kpi-stat-card">
        <div class="stat-icon" style="color:#1565c0;"><i class="fa-solid fa-box"></i></div>
        <div class="stat-value">{{ number_format($total_requerido, 0) }}</div>
        <div class="stat-label">Total Requerido</div>
        <div class="stat-sub">unidades</div>
    </div>
    <div class="kpi-stat-card">
        <div class="stat-icon" style="color:#2e7d32;"><i class="fa-solid fa-truck"></i></div>
        <div class="stat-value">{{ number_format($total_enviado, 0) }}</div>
        <div class="stat-label">Total Enviado</div>
        <div class="stat-sub">unidades</div>
    </div>
    <div class="kpi-stat-card">
        <div class="stat-icon" style="color:#e65100;"><i class="fa-solid fa-percent"></i></div>
        <div class="stat-value">{{ number_format($cumplimiento_bruto_porcentaje, 2) }}%</div>
        <div class="stat-label">Cumpl. Bruto</div>
        <div class="stat-sub">Enviado / Requerido</div>
    </div>
    <div class="kpi-stat-card kpi-stat-primary">
        <div class="stat-icon" style="color:#fff;"><i class="fa-solid fa-star"></i></div>
        <div class="stat-value">{{ number_format($cumplimiento_plan_porcentaje, 2) }}%</div>
        <div class="stat-label">Cumpl. del Plan</div>
        <div class="stat-sub">Efectivo / Válido</div>
    </div>
    <div class="kpi-stat-card">
        <div class="stat-icon" style="color:#c62828;"><i class="fa-solid fa-circle-exclamation"></i></div>
        <div class="stat-value" style="color:#dc3545;">{{ number_format($diferencia_total, 0) }}</div>
        <div class="stat-label">Diferencia</div>
        <div class="stat-sub">unidades</div>
    </div>
</div>

{{-- C) Bloque Explicativo del KPI Principal --}}
<div class="kpi-explicativo alert alert-info">
    <div class="d-flex align-items-start gap-3">
        <i class="fa-solid fa-circle-info fa-xl mt-1"></i>
        <div>
            <strong>Cumplimiento del Plan = Enviado Efectivo / Requerido Válido * 100</strong>
            <hr class="my-2">
            <div class="row small">
                <div class="col-md-6"><strong>Enviado Efectivo:</strong> {{ number_format($enviado_efectivo, 0) }} — suma de min(entregada, requerida) por item</div>
                <div class="col-md-6"><strong>Requerido Válido:</strong> {{ number_format($requerido_valido, 0) }} — suma de requerida de items sin estado 0 ni 1</div>
                <div class="col-12 text-muted mt-1">✗ No incluye excedentes, fuera de inventario (0) ni agotados (1)</div>
            </div>
        </div>
    </div>
</div>

{{-- D) Gráficos - 3 Columnas --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="kpi-chart-card">
            <div class="kpi-chart-title"><i class="fa-solid fa-chart-pie"></i> Cumplimiento Operativo</div>
            <div class="kpi-chart-subtitle">(Ítems trabajables)</div>
            <div class="chart-container">
                <canvas id="chartOperativo"></canvas>
            </div>
            <div class="kpi-chart-footer">Total Ítems trabajables: <strong>{{ $items_trabajables }}</strong></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-chart-card">
            <div class="kpi-chart-title"><i class="fa-solid fa-triangle-exclamation"></i> Inconvenientes</div>
            <div class="kpi-chart-subtitle">(No afectan el KPI)</div>
            <div class="chart-container">
                <canvas id="chartInconvenientes"></canvas>
            </div>
            <div class="kpi-chart-footer">
                Afectado: <strong>{{ number_format($requerido_afectado_inventario, 0) }}</strong> unidades
                <br>Impacto: <strong>{{ number_format($porcentaje_impacto_inventario, 2) }}%</strong> del plan
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-chart-card">
            <div class="kpi-chart-title"><i class="fa-solid fa-truck-fast"></i> Contenedores</div>
            <div class="kpi-chart-subtitle">Objetivo: 8 contenedores</div>
            <div class="chart-container">
                <canvas id="chartContenedores"></canvas>
            </div>
            <div class="kpi-chart-footer">
                Capacidad: <strong>{{ number_format($capacidad_operativa, 2) }}%</strong>
            </div>
        </div>
    </div>
</div>

{{-- E) Comentario Automático + Observaciones --}}
@if($contenedores_completados < $contenedores_planificados)
<div class="kpi-comentario alert alert-warning">
    <div class="d-flex align-items-start gap-3">
        <i class="fa-solid fa-triangle-exclamation fa-xl mt-1"></i>
        <div>
            <strong>Comentario del Sistema</strong>
            <p class="mb-0 mt-1">{{ $comentario_automatico }}@if($observaciones) Causa principal: {{ $observaciones }}@endif</p>
        </div>
    </div>
</div>
@else
<div class="kpi-comentario alert alert-success">
    <div class="d-flex align-items-start gap-3">
        <i class="fa-solid fa-circle-check fa-xl mt-1"></i>
        <div>
            <strong>Comentario del Sistema</strong>
            <p class="mb-0 mt-1">{{ $comentario_automatico }}@if($observaciones) Observaciones: {{ $observaciones }}@endif</p>
        </div>
    </div>
</div>
@endif

{{-- F) Tabla Detalle de Ítems del Plan --}}
<div class="kpi-table-section">
    <div class="kpi-table-header">
        <span><i class="fa-solid fa-table"></i> Detalle de Ítems del Plan</span>
    </div>

    <div class="kpi-badges-row">
        <span class="kpi-badge-item kpi-badge-completado"><i class="fa-solid fa-check"></i> {{ $total_completados_exactos }} Completados</span>
        <span class="kpi-badge-item kpi-badge-incompleto"><i class="fa-solid fa-minus"></i> {{ $total_incompletos_operativos }} Incompletos</span>
        <span class="kpi-badge-item kpi-badge-enviado-mas"><i class="fa-solid fa-arrow-up"></i> {{ $total_enviados_de_mas }} Enviados de más</span>
        <span class="kpi-badge-item kpi-badge-fuera-inventario"><i class="fa-solid fa-warehouse"></i> {{ $total_fuera_inventario }} F. Inventario</span>
        <span class="kpi-badge-item kpi-badge-agotado"><i class="fa-solid fa-battery-empty"></i> {{ $total_agotados }} Agotados</span>
        <span class="kpi-badge-item kpi-badge-total"><i class="fa-solid fa-cubes"></i> {{ $total_items_plan }} Total</span>
    </div>

    {{-- Tabla de ítems válidos (sin estado 0 ni 1) --}}
    <h6 style="padding:12px 20px 6px;font-size:13px;font-weight:700;color:var(--purple-700);margin:0;">Ítems considerados para el KPI</h6>
    <div class="table-responsive">
        <table class="kpi-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Cant. Requerida</th>
                    <th>Cant. Entregada</th>
                    <th>Diferencia</th>
                    <th>Clasificación KPI</th>
                    <th>Comentario</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                @if($item['estado_original'] !== '0' && $item['estado_original'] !== '1')
                <tr>
                    <td class="font-monospace">{{ $item['codigo'] }}</td>
                    <td>{{ $item['descripcion'] }}</td>
                    <td class="text-end">{{ number_format($item['cantidad_requerida'], 0) }}</td>
                    <td class="text-end">{{ number_format($item['cantidad_enviada'], 0) }}</td>
                    <td class="text-end {{ $item['diferencia'] > 0 ? 'kpi-text-negative' : '' }}">{{ number_format($item['diferencia'], 0) }}</td>
                    <td>
                        @php
                            $badgeClass = match($item['clasificacion_kpi']) {
                                'Completado' => 'kpi-badge-completado',
                                'Incompleto Operativo' => 'kpi-badge-incompleto',
                                'Enviado de más' => 'kpi-badge-enviado-mas',
                                'Fuera de Inventario' => 'kpi-badge-fuera-inventario',
                                'Agotado' => 'kpi-badge-agotado',
                                default => 'kpi-badge-total'
                            };
                        @endphp
                        <span class="kpi-badge-item {{ $badgeClass }}" style="font-size:11px;padding:3px 10px;">
                            {{ $item['clasificacion_kpi'] }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $item['comentario_causa'] }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Tabla de ítems con estado 0 o 1 --}}
    @php
        $itemsSinStock = array_filter($items, fn($i) => $i['estado_original'] === '0' || $i['estado_original'] === '1');
    @endphp
    @if(count($itemsSinStock) > 0)
    <div class="alert alert-warning" style="border-radius:0;margin:16px 20px 8px;font-size:13px;padding:12px 16px;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <strong>Nota importante:</strong> En la siguiente tabla se detallan los materiales que no pudieron ser despachados debido a la falta de inventario.
    </div>
    <h6 style="padding:0 20px 6px;font-size:13px;font-weight:700;color:var(--red-700);margin:0;">Ítems sin inventario (excluidos del KPI)</h6>
    <div class="table-responsive" style="padding-bottom:16px;">
        <table class="kpi-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Cant. Requerida</th>
                    <th>Cant. Entregada</th>
                    <th>Diferencia</th>
                    <th>Clasificación KPI</th>
                    <th>Comentario</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itemsSinStock as $item)
                <tr>
                    <td class="font-monospace">{{ $item['codigo'] }}</td>
                    <td>{{ $item['descripcion'] }}</td>
                    <td class="text-end">{{ number_format($item['cantidad_requerida'], 0) }}</td>
                    <td class="text-end">{{ number_format($item['cantidad_enviada'], 0) }}</td>
                    <td class="text-end kpi-text-negative">{{ number_format($item['diferencia'], 0) }}</td>
                    <td>
                        @php
                            $badgeClass = match($item['clasificacion_kpi']) {
                                'Fuera de Inventario' => 'kpi-badge-fuera-inventario',
                                'Agotado' => 'kpi-badge-agotado',
                                default => 'kpi-badge-total'
                            };
                        @endphp
                        <span class="kpi-badge-item {{ $badgeClass }}" style="font-size:11px;padding:3px 10px;">
                            {{ $item['clasificacion_kpi'] }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $item['comentario_causa'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- G) Gráficos Inferiores --}}
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="kpi-chart-card">
            <div class="kpi-chart-title"><i class="fa-solid fa-chart-pie"></i> Distribución por Clasificación KPI</div>
            <div class="chart-container">
                <canvas id="chartDistribucion"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="kpi-chart-card">
            <div class="kpi-chart-title"><i class="fa-solid fa-chart-bar"></i> Impacto del Inventario en el Plan</div>
            <div class="chart-container">
                <canvas id="chartImpacto"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- H) Observaciones Generales --}}
<div class="kpi-observaciones card">
    <div class="card-header bg-dark text-white">
        <i class="fa-solid fa-clipboard-list"></i> Observaciones Generales
    </div>
    <div class="card-body">
        <ol class="mb-3">
            @foreach($observaciones_generales as $obs)
            <li class="mb-2">{{ $obs }}</li>
            @endforeach
        </ol>
        <hr>
        <div>
            <strong>Firma / Aprobación:</strong>
            <div style="border-bottom:1px solid #000;min-width:200px;margin-top:15px;"></div>
            <small class="text-muted">Responsable de Operaciones — Fecha: ___/___/2026</small>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.register(ChartDataLabels);

    function getChartColors() {
        return {
            green: '#2e7d32',
            yellow: '#f9a825',
            blue: '#0288d1',
            red: '#d32f2f',
            orange: '#ed6c02',
            gray: '#9ca3af',
            dark: '#31245e',
            purple: '#6f5cc2',
            lightGray: '#e8e4f7',
            white: '#ffffff',
        };
    }

    function formatNumber(n) {
        return Number(n).toLocaleString('es-CR');
    }

    const c = getChartColors();

    // Chart 1: Cumplimiento Operativo (Donut)
    const ctx1 = document.getElementById('chartOperativo')?.getContext('2d');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Completados', 'Incompletos Operativos', 'Enviados de más'],
                datasets: [{
                    data: [{{ $total_completados_exactos }}, {{ $total_incompletos_operativos }}, {{ $total_enviados_de_mas }}],
                    backgroundColor: [c.green, c.yellow, c.blue],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '72%',
                animation: { animateRotate: true, duration: 1000 },
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                let total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                                let pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(2) : 0;
                                return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            }
                        }
                    },
                    datalabels: {
                        color: '#fff',
                        font: { weight: 'bold', size: 13 },
                        formatter: (value, ctx) => {
                            let total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                            return total > 0 ? (value / total * 100).toFixed(1) + '%' : '';
                        }
                    }
                }
            }
        });
    }

    // Chart 2: Inconvenientes (Donut)
    const ctx2 = document.getElementById('chartInconvenientes')?.getContext('2d');
    if (ctx2) {
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Fuera del Plan', 'Agotado'],
                datasets: [{
                    data: [{{ $total_fuera_inventario }}, {{ $total_agotados }}],
                    backgroundColor: [c.red, c.orange],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '72%',
                animation: { animateRotate: true, duration: 1000 },
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                let total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                                let pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(2) : 0;
                                return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            }
                        }
                    },
                    datalabels: {
                        color: '#fff',
                        font: { weight: 'bold', size: 13 },
                        formatter: (value, ctx) => {
                            let total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                            return total > 0 ? (value / total * 100).toFixed(1) + '%' : '';
                        }
                    }
                }
            }
        });
    }

    // Chart 3: Contenedores (Bar)
    const ctx3 = document.getElementById('chartContenedores')?.getContext('2d');
    if (ctx3) {
        new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: ['Completados', 'No Completados', 'Objetivo'],
                datasets: [{
                    data: [{{ $contenedores_completados }}, {{ $contenedores_no_completados }}, {{ $contenedores_planificados }}],
                    backgroundColor: [c.green, c.gray, c.dark],
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: { beginAtZero: true, max: 10, ticks: { stepSize: 1 } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.parsed.y + ' contenedores';
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#333',
                        font: { weight: 'bold', size: 14 },
                        formatter: (value) => value + ''
                    }
                }
            }
        });
    }

    // Chart 4: Distribución (Pie)
    const ctx4 = document.getElementById('chartDistribucion')?.getContext('2d');
    if (ctx4) {
        new Chart(ctx4, {
            type: 'pie',
            data: {
                labels: ['Completados', 'Incompletos Operativos', 'Enviados de más', 'Fuera del Plan', 'Agotado'],
                datasets: [{
                    data: [{{ $total_completados_exactos }}, {{ $total_incompletos_operativos }}, {{ $total_enviados_de_mas }}, {{ $total_fuera_inventario }}, {{ $total_agotados }}],
                    backgroundColor: [c.green, c.yellow, c.blue, c.red, c.orange],
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                animation: { animateRotate: true, duration: 1000 },
                plugins: {
                    legend: { position: 'right', labels: { padding: 8, usePointStyle: true, font: { size: 10 } } },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                let total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                                let pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(2) : 0;
                                return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            }
                        }
                    },
                    datalabels: {
                        color: '#fff',
                        font: { weight: 'bold', size: 11 },
                        formatter: (value, ctx) => {
                            let total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                            return total > 0 ? (value / total * 100).toFixed(1) + '%' : '';
                        }
                    }
                }
            }
        });
    }

    // Chart 5: Impacto Inventario (Horizontal Bar)
    const ctx5 = document.getElementById('chartImpacto')?.getContext('2d');
    if (ctx5) {
        const reqAfectado = {{ $requerido_afectado_inventario }};
        const reqTrabajable = {{ $requerido_trabajable }};
        const totalReq = {{ $total_requerido }};
        new Chart(ctx5, {
            type: 'bar',
            data: {
                labels: ['Total Requerido del Plan', 'Afectado por Inventario (0 y 1)', 'Trabajable (Para KPI Operativo)'],
                datasets: [{
                    data: [totalReq, reqAfectado, reqTrabajable],
                    backgroundColor: [c.blue, c.red, c.green],
                    borderRadius: 6,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                animation: { duration: 1000 },
                scales: {
                    x: { beginAtZero: true, ticks: { callback: (v) => formatNumber(v) } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                let total = ctx.dataset.data[0];
                                let pct = total > 0 ? ((ctx.parsed.x / total) * 100).toFixed(2) : 0;
                                return formatNumber(ctx.parsed.x) + ' (' + pct + '%)';
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#333',
                        font: { weight: 'bold', size: 11 },
                        formatter: (value) => formatNumber(value)
                    }
                }
            }
        });
    }
});

document.getElementById('form-export-pdf')?.addEventListener('submit', function() {
    if (typeof notifyInfo === 'function') {
        notifyInfo('Generando PDF...');
    }
});
</script>
@endsection
