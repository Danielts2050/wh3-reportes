<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Materiales 911</title>
    <style>
        @page { margin: 25px 30px; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 8.5pt; color: #333; line-height: 1.5; }
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

        .section-title {
            background: #31245e; color: #fff; padding: 5px 10px; font-size: 8.5pt;
            font-weight: bold; border-radius: 4px; margin: 14px 0 6px;
        }
        .sub-title { background: #6f5cc2; color: #fff; padding: 3px 8px; font-size: 7.5pt; font-weight: bold; border-radius: 3px; margin: 10px 0 4px; }

        table.info { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.info td { padding: 5px 8px; border: 1px solid #ddd; text-align: center; font-size: 8pt; width: 20%; }
        table.info .num { font-size: 11pt; font-weight: bold; color: #31245e; display: block; }
        table.info .lbl { font-size: 6.5pt; color: #666; }

        table.detalle { width: 100%; border-collapse: collapse; font-size: 6.5pt; margin: 6px 0; }
        table.detalle th { background: #31245e; color: #fff; padding: 4px 3px; text-align: center; font-weight: bold; font-size: 6.5pt; }
        table.detalle td { padding: 3px; border: 1px solid #ddd; text-align: center; }
        table.detalle tr:nth-child(even) { background: #f8f6ff; }

        .text-end { text-align: right; }
        .text-left { text-align: left; }

        .footer { margin-top: 16px; border-top: 1px solid #ddd; padding-top: 8px; font-size: 7pt; color: #444; }
        .firma { margin-top: 10px; }
        .firma .linea { border-bottom: 1px solid #000; width: 180px; margin-top: 3px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="brand">WH3 Reportes</div>
        <h1>REPORTE MATERIALES 911</h1>
        <p>RESUMEN DE DESPACHOS Y KPI OPERATIVOS</p>
    </div>

    <div class="meta-row">
        Generado: {{ $fecha_generacion ?? now()->format('d/m/Y H:i') }}
    </div>

    @php $totales = $resultado['totales']; @endphp

    <div class="summary-bar">
        <table>
            <tr>
                <td><span class="label">Registros</span><span class="value">{{ $totales['registros'] }}</span></td>
                <td><span class="label">Materiales</span><span class="value">{{ $totales['materiales_unicos'] }}</span></td>
                <td><span class="label">Cantidad</span><span class="value">{{ number_format($totales['total_cantidad'], 0) }}</span></td>
                <td><span class="label">Paletas</span><span class="value">{{ number_format($totales['total_paletas'], 0) }}</span></td>
                <td><span class="label">Días</span><span class="value">{{ $totales['dias'] }}</span></td>
            </tr>
        </table>
    </div>

    <div class="section-title">RESUMEN GENERAL</div>
    <table class="info">
        <tr>
            <td><span class="lbl">Registros</span><span class="num">{{ $totales['registros'] }}</span></td>
            <td><span class="lbl">Materiales Únicos</span><span class="num">{{ $totales['materiales_unicos'] }}</span></td>
            <td><span class="lbl">Cantidad Total</span><span class="num">{{ number_format($totales['total_cantidad'], 0) }}</span></td>
            <td><span class="lbl">Paletas Total</span><span class="num">{{ number_format($totales['total_paletas'], 0) }}</span></td>
            <td><span class="lbl">Prom. x Paleta</span><span class="num">{{ $totales['total_paletas'] > 0 ? number_format($totales['total_cantidad'] / $totales['total_paletas'], 2) : 0 }}</span></td>
        </tr>
    </table>

    <div class="section-title">DESPACHOS POR DÍA</div>
    <table class="detalle">
        <tr>
            <th>Fecha</th>
            <th>Registros</th>
            <th>Cantidad</th>
            <th>Paletas</th>
        </tr>
        @foreach($resultado['por_dia'] as $dia)
        <tr>
            <td>{{ $dia['fecha_str'] }}</td>
            <td>{{ $dia['registros'] }}</td>
            <td class="text-end">{{ number_format($dia['cantidad'], 0) }}</td>
            <td class="text-end">{{ number_format($dia['paletas'], 0) }}</td>
        </tr>
        @endforeach
    </table>

    <div class="sub-title">POR SOLICITANTE</div>
    <table class="detalle">
        <tr><th>Solicitante</th><th>Registros</th><th>Cantidad</th><th>Paletas</th></tr>
        @foreach($resultado['por_solicitante'] as $s)
        <tr>
            <td class="text-left">{{ $s['nombre'] }}</td>
            <td>{{ $s['registros'] }}</td>
            <td class="text-end">{{ number_format($s['cantidad'], 0) }}</td>
            <td class="text-end">{{ $s['paletas'] }}</td>
        </tr>
        @endforeach
    </table>

    <div class="sub-title">POR VÍA</div>
    <table class="detalle">
        <tr><th>Vía</th><th>Registros</th><th>Cantidad</th></tr>
        @foreach($resultado['por_via'] as $v)
        <tr>
            <td class="text-left">{{ $v['nombre'] }}</td>
            <td>{{ $v['registros'] }}</td>
            <td class="text-end">{{ number_format($v['cantidad'], 0) }}</td>
        </tr>
        @endforeach
    </table>

    <div class="sub-title">POR STATUS</div>
    <table class="detalle">
        <tr><th>Status</th><th>Registros</th><th>Cantidad</th><th>Paletas</th></tr>
        @foreach($resultado['por_status'] as $st)
        <tr>
            <td class="text-left">{{ $st['status'] }}</td>
            <td>{{ $st['registros'] }}</td>
            <td class="text-end">{{ number_format($st['cantidad'], 0) }}</td>
            <td class="text-end">{{ $st['paletas'] }}</td>
        </tr>
        @endforeach
    </table>

    <div class="section-title">PLANIFICADO / PRIORIDAD</div>
    <table class="detalle">
        <tr>
            <th>Atributo</th>
            <th>Valor</th>
            <th>Registros</th>
            <th>%</th>
        </tr>
        @foreach($resultado['por_planificado'] as $p)
        <tr><td>Planificado</td><td>{{ $p['valor'] }}</td><td>{{ $p['registros'] }}</td><td>{{ $p['porcentaje'] }}%</td></tr>
        @endforeach
        @foreach($resultado['por_prioridad'] as $p)
        <tr><td>Prioridad</td><td>{{ $p['valor'] }}</td><td>{{ $p['registros'] }}</td><td>{{ $p['porcentaje'] }}%</td></tr>
        @endforeach
    </table>

    <div class="section-title">DETALLE DE DESPACHOS</div>
    <table class="detalle">
        <thead>
            <tr>
                <th>Material</th>
                <th>Fecha</th>
                <th>Cantidad</th>
                <th>Paletas</th>
                <th>Prio.</th>
                <th>Plan.</th>
                <th>Vía</th>
                <th>Solicitante</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resultado['items'] as $item)
            <tr>
                <td class="text-left">{{ $item['material'] }}</td>
                <td>{{ $item['fecha_str'] }}</td>
                <td class="text-end">{{ number_format($item['cantidad'], 0) }}</td>
                <td class="text-end">{{ $item['paletas'] }}</td>
                <td>{{ $item['prioridad'] ?: '—' }}</td>
                <td>{{ $item['planificado'] ?: '—' }}</td>
                <td class="text-left">{{ $item['via'] }}</td>
                <td class="text-left">{{ $item['solicitante'] }}</td>
                <td class="text-left">{{ $item['status_label'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="firma">
            <strong>Firma / Aprobación:</strong>
            <div class="linea"></div>
            <small>Responsable de Operaciones — Fecha: ___/___/2026</small>
        </div>
    </div>

</body>
</html>