<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Plan de Carga</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; }
        table { border-collapse: collapse; margin-bottom: 20px; }
        caption { font-weight: bold; text-align: left; padding: 6px 0; font-size: 12pt; }
        th {
            border: 1px solid #000; background-color: #FFF200;
            font-weight: bold; text-align: center; vertical-align: middle;
            padding: 5px 8px;
        }
        td {
            border: 1px solid #000; text-align: center; vertical-align: middle;
            padding: 4px 8px;
        }
        h2 { font-size: 14pt; margin: 24px 0 10px; }
        .resaltar { background-color: #FFF200; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Plan de Carga — Por Contenedor</h2>
    @php $contenedores = $estado['contenedores'] ?? []; @endphp
    @foreach($contenedores as $idx => $cont)
    @php
        $nombre = $cont['nombre'] ?? ('Contenedor ' . ($idx + 1));
        $capacidad = $cont['capacidad'] ?? 34;
        $totalAsig = array_sum(array_column($cont['items'] ?? [], 'palets'));
        $pct = $capacidad > 0 ? round(($totalAsig / $capacidad) * 100, 1) : 0;
    @endphp
    <table cellspacing="0" cellpadding="4" style="width:auto;">
        <caption>{{ $nombre }} — {{ (int)$totalAsig }}/{{ $capacidad }} ({{ $pct }}%)</caption>
        <thead>
            <tr>
                <th style="width:160px;">Material</th>
                <th style="width:100px;">Palets</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cont['items'] ?? [] as $item)
            <tr>
                <td>{{ $item['material'] }}</td>
                <td>{{ (int)$item['palets'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2" style="color:#666; font-style:italic;">Sin asignaciones</td>
            </tr>
            @endforelse
            <tr class="resaltar">
                <td>TOTAL</td>
                <td>{{ (int)$totalAsig }}</td>
            </tr>
        </tbody>
    </table>
    @endforeach

    @php $materiales = $estado['materiales'] ?? []; @endphp
    @php $noAsignados = array_filter($materiales, fn($m) => ($m['palets_disponibles'] - $m['palets_asignados']) > 0); @endphp
    @if(count($noAsignados) > 0)
    <h2>Materiales No Asignados</h2>
    <table cellspacing="0" cellpadding="4" style="width:100%;">
        <thead>
            <tr>
                <th>Material</th>
                <th>Palets Requeridos</th>
                <th>Palets Disponibles</th>
                <th>Palets Asignados</th>
                <th>Palets Restantes</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($noAsignados as $m)
            <tr>
                <td>{{ $m['material'] }}</td>
                <td>{{ (int)$m['palets_requeridos'] }}</td>
                <td>{{ (int)$m['palets_disponibles'] }}</td>
                <td>{{ (int)$m['palets_asignados'] }}</td>
                <td>{{ (int)($m['palets_disponibles'] - $m['palets_asignados']) }}</td>
                <td>{{ $m['estado'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <h2>Datos Originales</h2>
    <table cellspacing="0" cellpadding="4" style="width:100%;">
        <thead>
            <tr>
                <th>Material</th>
                <th>Cant. Requerida</th>
                <th>Qty/Pallet</th>
                <th>Cant. Disponible</th>
                <th>Palets Req.</th>
                <th>Palets Disp.</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($materiales as $m)
            <tr>
                <td>{{ $m['material'] }}</td>
                <td>{{ number_format($m['cantidad_requerida'], 2) }}</td>
                <td>{{ number_format($m['qty_per_pallet'], 4) }}</td>
                <td>{{ number_format($m['cantidad_disponible'], 2) }}</td>
                <td>{{ (int)$m['palets_requeridos'] }}</td>
                <td>{{ (int)$m['palets_disponibles'] }}</td>
                <td>{{ $m['estado'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
