<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Plan de Carga</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th { background: #31245e; color: #fff; padding: 6px 8px; text-align: center; font-size: 9pt; }
        td { border: 1px solid #ccc; padding: 4px 6px; text-align: center; font-size: 9pt; }
        tr:nth-child(even) { background: #f8f6ff; }
        h2 { color: #31245e; font-size: 13pt; margin: 20px 0 8px; }
        .subtle { color: #888; font-size: 8pt; }
        .bg-yellow { background: #fff3cd; }
    </style>
</head>
<body>
    <h2>Plan de Carga — Por Contenedor</h2>
    @php $contenedores = $estado['contenedores'] ?? []; @endphp
    @foreach($contenedores as $idx => $cont)
    @php
        $nombre = $cont['nombre'] ?? ('Contenedor ' . ($idx + 1));
        $capacidad = $cont['capacidad'] ?? 24;
        $totalAsig = array_sum(array_column($cont['items'] ?? [], 'palets'));
        $pct = $capacidad > 0 ? round(($totalAsig / $capacidad) * 100, 1) : 0;
    @endphp
    <table>
        <caption style="font-weight:bold;text-align:left;padding:6px 0;">
            {{ $nombre }} — {{ number_format($totalAsig, 2) }}/{{ $capacidad }} ({{ $pct }}%)
        </caption>
        <thead>
            <tr>
                <th>Material</th>
                <th>Palets Asignados</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cont['items'] ?? [] as $item)
            <tr>
                <td>{{ $item['material'] }}</td>
                <td>{{ number_format($item['palets'], 3) }}</td>
            </tr>
            @empty
            <tr><td colspan="2" class="subtle">Sin asignaciones</td></tr>
            @endforelse
        </tbody>
    </table>
    @endforeach

    @php $materiales = $estado['materiales'] ?? []; @endphp
    @php $noAsignados = array_filter($materiales, fn($m) => ($m['palets_disponibles'] - $m['palets_asignados']) > 0); @endphp
    @if(count($noAsignados) > 0)
    <h2>Materiales No Asignados</h2>
    <table>
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
                <td>{{ number_format($m['palets_requeridos'], 3) }}</td>
                <td>{{ number_format($m['palets_disponibles'], 3) }}</td>
                <td>{{ number_format($m['palets_asignados'], 3) }}</td>
                <td>{{ number_format($m['palets_disponibles'] - $m['palets_asignados'], 3) }}</td>
                <td>{{ $m['estado'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <h2>Datos Originales</h2>
    <table>
        <thead>
            <tr>
                <th>Material</th>
                <th>Cant. Requerida</th>
                <th>Qty/Pallet</th>
                <th>Cant. Disponible</th>
                <th>Palets Requeridos</th>
                <th>Palets Totales</th>
                <th>Palets Bloqueados</th>
                <th>Palets Disponibles</th>
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
                <td>{{ number_format($m['palets_requeridos'], 3) }}</td>
                <td>{{ number_format($m['palets_totales'], 3) }}</td>
                <td>{{ number_format($m['palets_bloqueados'], 3) }}</td>
                <td>{{ number_format($m['palets_disponibles'], 3) }}</td>
                <td>{{ $m['estado'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
