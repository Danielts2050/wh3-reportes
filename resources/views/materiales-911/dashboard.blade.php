<div class="m9-toolbar">
    <div class="m9-toolbar-title">
        <i class="fa-solid fa-chart-simple"></i> Dashboard Materiales 911
    </div>
    <div class="m9-toolbar-actions">
        <form method="POST" action="/materiales-911/exportar-pdf" id="form-export-pdf">
            @csrf
            <input type="hidden" name="datos" value="{{ $datos }}">
            <button class="btn btn-danger" id="btnExportarPdf">
                <i class="fa-solid fa-file-pdf"></i> Exportar PDF
            </button>
        </form>
    </div>
</div>

{{-- A) Totales Generales --}}
<div class="m9-resumen">
    <div class="m9-stat">
        <div class="m9-stat-icon" style="color:#2e7d32;"><i class="fa-solid fa-file-lines"></i></div>
        <div class="m9-stat-value">{{ $resultado['totales']['registros'] }}</div>
        <div class="m9-stat-label">Registros</div>
    </div>
    <div class="m9-stat">
        <div class="m9-stat-icon" style="color:#1565c0;"><i class="fa-solid fa-box"></i></div>
        <div class="m9-stat-value">{{ $resultado['totales']['materiales_unicos'] }}</div>
        <div class="m9-stat-label">Materiales Únicos</div>
    </div>
    <div class="m9-stat m9-stat-primary">
        <div class="m9-stat-icon" style="color:#fff;"><i class="fa-solid fa-weight-hanging"></i></div>
        <div class="m9-stat-value">{{ number_format($resultado['totales']['total_cantidad'], 0) }}</div>
        <div class="m9-stat-label">Cantidad Total</div>
    </div>
    <div class="m9-stat">
        <div class="m9-stat-icon" style="color:#e65100;"><i class="fa-solid fa-pallet"></i></div>
        <div class="m9-stat-value">{{ number_format($resultado['totales']['total_paletas'], 0) }}</div>
        <div class="m9-stat-label">Paletas Total</div>
    </div>
    <div class="m9-stat">
        <div class="m9-stat-icon" style="color:#6f5cc2;"><i class="fa-regular fa-calendar-days"></i></div>
        <div class="m9-stat-value">{{ $resultado['totales']['dias'] }}</div>
        <div class="m9-stat-label">Días</div>
    </div>
</div>

{{-- Efectividad (si ratio cantidad/paletas) --}}
@php $efectividad = $resultado['totales']['total_paletas'] > 0 ? $resultado['totales']['total_cantidad'] / $resultado['totales']['total_paletas'] : 0; @endphp
<div class="m9-explicativo alert alert-info">
    <div class="d-flex align-items-start gap-3">
        <i class="fa-solid fa-circle-info fa-xl mt-1"></i>
        <div>
            <strong>Promedio por paleta:</strong> {{ number_format($efectividad, 2) }} unidades/paleta
            <div class="small text-muted mt-1">Total cantidad {{ number_format($resultado['totales']['total_cantidad'], 0) }} / Total paletas {{ number_format($resultado['totales']['total_paletas'], 0) }}</div>
        </div>
    </div>
</div>

{{-- Despachos por Día --}}
<div class="m9-section">
    <div class="m9-section-title"><i class="fa-regular fa-calendar-days"></i> Despachos por Día</div>
    <div class="m9-grid m9-grid-day">
        @foreach($resultado['por_dia'] as $dia)
        <div class="m9-day">
            <div class="m9-day-head">{{ $dia['fecha_str'] }}</div>
            <div class="m9-day-body">
                <div><i class="fa-solid fa-box"></i> {{ number_format($dia['cantidad'], 0) }}</div>
                <div><i class="fa-solid fa-pallet"></i> {{ number_format($dia['paletas'], 0) }}</div>
                <div><span class="m9-tag">{{ $dia['registros'] }} registros</span></div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Por Solicitante y Por Vía --}}
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="m9-chart-card">
            <div class="m9-chart-title"><i class="fa-solid fa-users"></i> Por Solicitante</div>
            <div class="table-responsive">
                <table class="m9-table">
                    <thead>
                        <tr><th>Solicitante</th><th class="text-end">Cantidad</th><th class="text-end">Paletas</th><th class="text-end">Reg.</th></tr>
                    </thead>
                    <tbody>
                        @foreach($resultado['por_solicitante'] as $s)
                        <tr>
                            <td>{{ $s['nombre'] }}</td>
                            <td class="text-end">{{ number_format($s['cantidad'], 0) }}</td>
                            <td class="text-end">{{ $s['paletas'] }}</td>
                            <td class="text-end">{{ $s['registros'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="m9-chart-card">
            <div class="m9-chart-title"><i class="fa-solid fa-route"></i> Por Vía</div>
            <div class="table-responsive">
                <table class="m9-table">
                    <thead>
                        <tr><th>Vía</th><th class="text-end">Cantidad</th><th class="text-end">Reg.</th></tr>
                    </thead>
                    <tbody>
                        @foreach($resultado['por_via'] as $v)
                        <tr>
                            <td>{{ $v['nombre'] }}</td>
                            <td class="text-end">{{ number_format($v['cantidad'], 0) }}</td>
                            <td class="text-end">{{ $v['registros'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Por Status, Planificado y Prioridad --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="m9-chart-card">
            <div class="m9-chart-title"><i class="fa-solid fa-flag"></i> Por Status</div>
            <div class="chart-container">
                <canvas id="chartStatus"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="m9-chart-card">
            <div class="m9-chart-title"><i class="fa-solid fa-calendar-check"></i> Planificado</div>
            <div class="chart-container">
                <canvas id="chartPlanificado"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="m9-chart-card">
            <div class="m9-chart-title"><i class="fa-solid fa-bolt"></i> Prioridad</div>
            <div class="chart-container">
                <canvas id="chartPrioridad"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Detalle de Ítems --}}
<div class="m9-section">
    <div class="m9-section-title"><i class="fa-solid fa-table"></i> Detalle de Despachos</div>
    <div class="table-responsive">
        <table class="m9-table">
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Fecha</th>
                    <th class="text-end">Cantidad</th>
                    <th class="text-end">Paletas</th>
                    <th>Prioridad</th>
                    <th>Planificado</th>
                    <th>Vía</th>
                    <th>Solicitante</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultado['items'] as $item)
                <tr>
                    <td class="font-monospace">{{ $item['material'] }}</td>
                    <td>{{ $item['fecha_str'] }}</td>
                    <td class="text-end">{{ number_format($item['cantidad'], 0) }}</td>
                    <td class="text-end">{{ $item['paletas'] }}</td>
                    <td><span class="m9-tag">{{ $item['prioridad'] ?: '—' }}</span></td>
                    <td><span class="m9-tag">{{ $item['planificado'] ?: '—' }}</span></td>
                    <td>{{ $item['via'] }}</td>
                    <td>{{ $item['solicitante'] }}</td>
                    <td><span class="m9-badge m9-badge-{{ $item['status_key'] === 'Q' ? 'q' : 'ok' }}">{{ $item['status_label'] }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var c = {green:'#2e7d32',yellow:'#f9a825',purple:'#6f5cc2',dark:'#31245e',red:'#d32f2f',gray:'#9ca3af'};

    function makeDonut(canvasId, labels, data, bg) {
        var ctx = document.getElementById(canvasId);
        if(!ctx) return;
        new Chart(ctx, {
            type:'doughnut',
            data:{labels:labels,datasets:[{data:data,backgroundColor:bg,borderWidth:0}]},
            options:{
                responsive:true,maintainAspectRatio:true,cutout:'72%',
                plugins:{
                    legend:{position:'bottom',labels:{usePointStyle:true,font:{size:10}}},
                    tooltip:{callbacks:{label:function(cc){var t=cc.dataset.data.reduce(function(a,b){return a+b;},0);var p=t>0?((cc.parsed/t)*100).toFixed(2):0;return cc.label+': '+cc.parsed+' ('+p+'%)';}}}
                }
            }
        });
    }

    var status = @json(array_column($resultado['por_status'], 'status'));
    var statusN = @json(array_column($resultado['por_status'], 'registros'));
    makeDonut('chartStatus', status, statusN, [c.green, c.yellow, c.gray, c.red]);

    var plan = @json(array_column($resultado['por_planificado'], 'valor'));
    var planN = @json(array_column($resultado['por_planificado'], 'registros'));
    makeDonut('chartPlanificado', plan, planN, [c.dark, c.yellow, c.gray]);

    var prio = @json(array_column($resultado['por_prioridad'], 'valor'));
    var prioN = @json(array_column($resultado['por_prioridad'], 'registros'));
    makeDonut('chartPrioridad', prio, prioN, [c.red, c.green, c.gray]);
});
</script>