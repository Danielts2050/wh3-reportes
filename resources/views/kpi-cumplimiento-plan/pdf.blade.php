<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte KPI – Cumplimiento del Plan</title>
    <style>
        @page { margin: 25px 30px; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9pt; color: #333; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #31245e; padding-bottom: 10px; margin-bottom: 12px; }
        .header h1 { font-size: 15pt; color: #31245e; margin: 0 0 2px; }
        .header p { font-size: 7.5pt; color: #444; margin: 0; }
        .header .brand { font-size: 9pt; font-weight: bold; color: #6f5cc2; }
        .meta-row { font-size: 7pt; color: #333; margin-bottom: 10px; text-align: center; }

        .summary-bar { background: #31245e; color: #fff; padding: 8px 10px; border-radius: 6px; margin-bottom: 16px; }
        .summary-bar table { width: 100%; }
        .summary-bar td { text-align: center; font-size: 7.5pt; padding: 3px 5px; }
        .summary-bar .label { font-size: 6pt; opacity: .8; display: block; }
        .summary-bar .value { font-weight: bold; font-size: 9pt; }

        .hero-kpi {
            background: #f8f6ff;
            border: 2px solid #31245e;
            border-radius: 10px;
            padding: 16px 18px;
            margin-bottom: 14px;
        }
        .hero-kpi .hero-title {
            font-size: 9pt; font-weight: 700; color: #31245e;
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px;
        }
        .hero-kpi .hero-title span { color: #6f5cc2; font-weight: 400; text-transform: none; letter-spacing: 0; }

        .bar-container {
            width: 100%; height: 36px; background: #e9ecef; border-radius: 18px;
            position: relative; overflow: hidden; margin-bottom: 10px;
        }
        .bar-fill {
            height: 100%; background: linear-gradient(90deg, #28a745, #20c997);
            border-radius: 18px; display: flex; align-items: center; justify-content: center;
        }
        .bar-fill .pct { font-size: 14pt; font-weight: 800; color: #1a3a5c; }

        .hero-data {
            display: flex; justify-content: center; gap: 24px; margin-top: 10px; font-size: 8pt;
        }
        .hero-data .num { font-size: 14pt; font-weight: 800; color: #31245e; display: block; }
        .hero-data .hl { color: #28a745; }
        .hero-data .gap { color: #dc3545; }
        .hero-data .lbl { font-size: 6.5pt; text-transform: uppercase; color: #444; }

        .hero-sub {
            text-align: center; margin-top: 10px; padding-top: 8px;
            border-top: 1px dashed #bbb; font-size: 7pt; color: #555;
        }

        .section-title {
            background: #31245e; color: #fff; padding: 5px 10px; font-size: 8.5pt;
            font-weight: bold; border-radius: 4px; margin: 14px 0 6px;
        }

        table.info { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.info td { padding: 5px 8px; border: 1px solid #ddd; text-align: center; font-size: 8pt; width: 25%; }
        table.info .num { font-size: 11pt; font-weight: bold; color: #31245e; display: block; }
        table.info .lbl { font-size: 6.5pt; color: #666; }

        table.info td.subtle { color: #777; }
        table.info td.subtle .num { color: #777; font-weight: 400; font-size: 9pt; }

        .comentario { background: #fff3e0; border: 1px solid #ffcc80; padding: 7px 10px; border-radius: 5px; margin: 10px 0; font-size: 7.5pt; }
        .comentario.success { background: #e8f5e9; border-color: #81c784; }

        table.detalle { width: 100%; border-collapse: collapse; font-size: 6.5pt; margin: 6px 0; }
        table.detalle th { background: #31245e; color: #fff; padding: 4px 3px; text-align: center; font-weight: bold; font-size: 6.5pt; }
        table.detalle td { padding: 3px; border: 1px solid #ddd; text-align: center; }
        table.detalle tr:nth-child(even) { background: #f8f6ff; }

        .badge { display: inline-block; padding: 1px 6px; border-radius: 8px; font-size: 6pt; font-weight: bold; color: #fff; }
        .badge-green { background: #28a745; }
        .badge-yellow { background: #ffc107; color: #333; }
        .badge-blue { background: #17a2b8; }
        .badge-red { background: #dc3545; }
        .badge-orange { background: #fd7e14; }
        .badge-gray { background: #6c757d; }

        .badges-row { margin: 5px 0 6px; }
        .badges-row .badge { margin-right: 2px; }

        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .text-negative { color: #dc3545; font-weight: bold; }

        .footer { margin-top: 16px; border-top: 1px solid #ddd; padding-top: 8px; font-size: 7pt; color: #444; }
        .firma { margin-top: 10px; }
        .firma .linea { border-bottom: 1px solid #000; width: 180px; margin-top: 3px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="brand">WH3 Reportes</div>
        <h1>REPORTE KPI – CUMPLIMIENTO DEL PLAN</h1>
        <p>REPORTE DIARIO</p>
    </div>

    <div class="meta-row">
        {{ \Carbon\Carbon::parse($fecha_plan)->format('d/m/Y') }} &nbsp;|&nbsp;
        Generado: {{ $fecha_generacion ?? now()->format('d/m/Y H:i') }} &nbsp;|&nbsp;
        Contenedores: {{ $contenedores_completados }}/{{ $contenedores_planificados }} ({{ number_format($capacidad_operativa, 2) }}%)
    </div>

    <div class="summary-bar">
        <table>
            <tr>
                <td><span class="label">Fecha del Plan</span><span class="value">{{ \Carbon\Carbon::parse($fecha_plan)->format('d/m/Y') }}</span></td>
                <td><span class="label">Cont. Planificados</span><span class="value">{{ $contenedores_planificados }}</span></td>
                <td><span class="label">Cont. Completados</span><span class="value">{{ $contenedores_completados }}</span></td>
                <td><span class="label">Capacidad Operativa</span><span class="value">{{ number_format($capacidad_operativa, 2) }}%</span></td>
                <td><span class="label">Estado</span><span class="value">{{ $estado_dia }}</span></td>
            </tr>
        </table>
    </div>

    {{-- HERO: KPI Principal con gráfica de barra CSS --}}
    <div class="hero-kpi">
        <div class="hero-title">★ Cumplimiento del Plan <span>— KPI Principal</span></div>

        <div class="bar-container">
            @php $brecha = max(0, 100 - $cumplimiento_plan_porcentaje); @endphp
            <div class="bar-fill" style="width: {{ max($cumplimiento_plan_porcentaje, 1) }}%;">
                @if($cumplimiento_plan_porcentaje > 15)
                    <span class="pct">{{ number_format($cumplimiento_plan_porcentaje, 2) }}%</span>
                @endif
            </div>
            @if($brecha > 0 && $cumplimiento_plan_porcentaje <= 15)
                <div style="position:absolute;right:8px;top:50%;transform:translateY(-50%);font-size:14pt;font-weight:800;color:#1a3a5c;">
                    {{ number_format($cumplimiento_plan_porcentaje, 2) }}%
                </div>
            @endif
        </div>

        <div class="hero-data">
            <div>
                <span class="num hl">{{ number_format($enviado_efectivo, 0) }}</span>
                <span class="lbl">Enviado Efectivo</span>
            </div>
            <div>
                <span class="num">{{ number_format($requerido_valido, 0) }}</span>
                <span class="lbl">Requerido Válido</span>
            </div>
            <div>
                <span class="num gap">{{ number_format(max(0, $requerido_valido - $enviado_efectivo), 0) }}</span>
                <span class="lbl">Brecha (unid.)</span>
            </div>
        </div>

        <div class="hero-sub">
            Enviado Efectivo = suma de min(entregada, requerida) por item &nbsp;|&nbsp;
            Requerido Válido = suma de requerida sin estado 0 ni 1
        </div>
    </div>

    {{-- Comentario --}}
    @if($contenedores_completados < $contenedores_planificados)
    <div class="comentario">
        <strong>⚠ Comentario:</strong> {{ $comentario_automatico }}@if($observaciones) Causa principal: {{ $observaciones }}@endif
    </div>
    @else
    <div class="comentario success">
        <strong>✔ Comentario:</strong> {{ $comentario_automatico }}@if($observaciones) Observaciones: {{ $observaciones }}@endif
    </div>
    @endif

    {{-- Resumen General (sin protagonismo) --}}
    <div class="section-title">RESUMEN GENERAL</div>
    <table class="info">
        <tr>
            <td><span class="lbl">Total Ítems</span><span class="num">{{ $total_items_plan }}</span></td>
            <td><span class="lbl">Total Requerido</span><span class="num">{{ number_format($total_requerido, 0) }}</span></td>
            <td><span class="lbl">Total Enviado</span><span class="num">{{ number_format($total_enviado, 0) }}</span></td>
            <td class="subtle"><span class="lbl">Cumpl. Bruto</span><span class="num">{{ number_format($cumplimiento_bruto_porcentaje, 2) }}%</span></td>
        </tr>
    </table>

    {{-- Clasificación de Ítems --}}
    <div class="section-title">CLASIFICACIÓN DE ÍTEMS</div>
    <table class="detalle">
        <tr>
            <th>Categoría</th>
            <th>Items</th>
            <th>%</th>
            <th>Afecta</th>
        </tr>
        <tr>
            <td><strong>Completados Exactos</strong></td>
            <td>{{ $total_completados_exactos }}</td>
            <td>{{ $total_items_plan > 0 ? number_format(($total_completados_exactos / $total_items_plan) * 100, 2) : 0 }}%</td>
            <td>Sí</td>
        </tr>
        <tr>
            <td>Incompletos Operativos</td>
            <td>{{ $total_incompletos_operativos }}</td>
            <td>{{ $total_items_plan > 0 ? number_format(($total_incompletos_operativos / $total_items_plan) * 100, 2) : 0 }}%</td>
            <td>Sí</td>
        </tr>
        <tr style="color:#777;font-size:6.5pt;">
            <td>Enviados de más <span style="font-style:italic;">(excedente)</span></td>
            <td>{{ $total_enviados_de_mas }}</td>
            <td>{{ $total_items_plan > 0 ? number_format(($total_enviados_de_mas / $total_items_plan) * 100, 2) : 0 }}%</td>
            <td>No</td>
        </tr>
        <tr>
            <td style="color:#dc3545;">Fuera del Plan</td>
            <td>{{ $total_fuera_inventario }}</td>
            <td>{{ $total_items_plan > 0 ? number_format(($total_fuera_inventario / $total_items_plan) * 100, 2) : 0 }}%</td>
            <td>No</td>
        </tr>
        <tr>
            <td style="color:#fd7e14;">Agotado</td>
            <td>{{ $total_agotados }}</td>
            <td>{{ $total_items_plan > 0 ? number_format(($total_agotados / $total_items_plan) * 100, 2) : 0 }}%</td>
            <td>No</td>
        </tr>
        <tr style="font-weight:bold;background:#f0ecfd;">
            <td>TOTAL</td>
            <td>{{ $total_items_plan }}</td>
            <td>100%</td>
            <td></td>
        </tr>
    </table>

    {{-- Detalle de Ítems --}}
    <div class="section-title">DETALLE DE ÍTEMS DEL PLAN</div>
    <div class="badges-row">
        <span class="badge badge-green">{{ $total_completados_exactos }} Comp.</span>
        <span class="badge badge-yellow">{{ $total_incompletos_operativos }} Incomp.</span>
        <span class="badge badge-blue" style="opacity:.75;">{{ $total_enviados_de_mas }} Exc.</span>
        <span class="badge badge-red">{{ $total_fuera_inventario }} F. Inv.</span>
        <span class="badge badge-orange">{{ $total_agotados }} Agot.</span>
        <span class="badge badge-gray">{{ $total_items_plan }} Total</span>
    </div>

    <table class="detalle">
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th>Req.</th>
                <th>Entr.</th>
                <th>Dif.</th>
                <th>KPI</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            @if($item['estado_original'] !== '0' && $item['estado_original'] !== '1')
            <tr>
                <td>{{ $item['codigo'] }}</td>
                <td style="text-align:left;">{{ $item['descripcion'] }}</td>
                <td class="text-end">{{ number_format($item['cantidad_requerida'], 0) }}</td>
                <td class="text-end">{{ number_format($item['cantidad_enviada'], 0) }}</td>
                <td class="text-end {{ $item['diferencia'] > 0 ? 'text-negative' : '' }}">{{ number_format($item['diferencia'], 0) }}</td>
                <td>
                    @php
                        $badgePdf = match($item['clasificacion_kpi']) {
                            'Completado' => 'badge-green',
                            'Incompleto Operativo' => 'badge-yellow',
                            'Enviado de más' => 'badge-blue',
                            'Fuera de Inventario' => 'badge-red',
                            'Agotado' => 'badge-orange',
                            default => 'badge-gray'
                        };
                    @endphp
                    <span class="badge {{ $badgePdf }}">{{ $item['clasificacion_kpi'] }}</span>
                </td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>

    {{-- Tabla de ítems sin inventario --}}
    @php
        $itemsSinStock = array_filter($items, fn($i) => $i['estado_original'] === '0' || $i['estado_original'] === '1');
    @endphp
    @if(count($itemsSinStock) > 0)
    <div class="section-title" style="margin-top:18px;border:none;font-size:8pt;padding:4px 8px;background:#fff3e0;color:#92400e;border-radius:4px;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        Nota importante: En la siguiente tabla se detallan los materiales que no pudieron ser despachados debido a la falta de inventario.
    </div>
    <table class="detalle" style="margin-top:8px;">
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th>Req.</th>
                <th>Entr.</th>
                <th>Dif.</th>
                <th>KPI</th>
            </tr>
        </thead>
        <tbody>
            @foreach($itemsSinStock as $item)
            <tr>
                <td>{{ $item['codigo'] }}</td>
                <td style="text-align:left;">{{ $item['descripcion'] }}</td>
                <td class="text-end">{{ number_format($item['cantidad_requerida'], 0) }}</td>
                <td class="text-end">{{ number_format($item['cantidad_enviada'], 0) }}</td>
                <td class="text-end text-negative">{{ number_format($item['diferencia'], 0) }}</td>
                <td>
                    @php
                        $badgePdf = $item['clasificacion_kpi'] === 'Fuera de Inventario' ? 'badge-red' : 'badge-orange';
                    @endphp
                    <span class="badge {{ $badgePdf }}">{{ $item['clasificacion_kpi'] }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Observaciones --}}
    <div class="section-title">OBSERVACIONES GENERALES</div>
    <div class="footer">
        <ol style="margin-bottom:6px;padding-left:16px;">
            @foreach($observaciones_generales as $obs)
            <li style="margin-bottom:2px;">{{ $obs }}</li>
            @endforeach
        </ol>
        <div class="firma">
            <strong>Firma / Aprobación:</strong>
            <div class="linea"></div>
            <small>Responsable de Operaciones — Fecha: ___/___/2026</small>
        </div>
    </div>

</body>
</html>
