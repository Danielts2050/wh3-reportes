<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Plan de Carga</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; margin: 20px; }
        h2 { font-size: 14pt; margin: 24px 0 10px; }
        table { border-collapse: collapse; margin-bottom: 24px; }
        th {
            border: 1px solid #000; background-color: #FFF200;
            font-weight: bold; text-align: center; vertical-align: middle;
            padding: 5px 10px; font-size: 11pt;
        }
        .sub-th {
            background-color: #FFF9C4; font-weight: bold;
            border: 1px solid #000; text-align: center; vertical-align: middle;
            padding: 4px 8px;
        }
        td {
            border: 1px solid #000; text-align: center; vertical-align: middle;
            padding: 4px 8px; font-size: 10.5pt;
        }
        .resaltar { background-color: #FFF200; font-weight: bold; }
        caption { font-weight: bold; text-align: left; padding: 8px 0 4px; font-size: 12pt; }
    </style>
</head>
<body>
    <h2>Plan de Carga</h2>

    @php
        $contenedores = $estado['contenedores'] ?? [];
        $materiales = $estado['materiales'] ?? [];
    @endphp

    @foreach($contenedores as $idx => $cont)
    @php
        $items = $cont['items'] ?? [];
        if (count($items) === 0) continue;
        $nombre = $cont['nombre'] ?? ('Contenedor ' . ($idx + 1));
        $capacidad = $cont['capacidad'] ?? 34;
        $totalPalets = array_sum(array_column($items, 'palets'));
    @endphp

    <table cellspacing="0" cellpadding="4" style="width:auto;">
        <caption>{{ $nombre }}</caption>
        <tr>
            <th style="width:160px;">Material</th>
            <th style="width:110px;">Cantidad</th>
            <th style="width:80px;">Palets</th>
            <th style="width:140px;">Referencia</th>
        </tr>
        @foreach($items as $item)
        @php
            $matData = collect($materiales)->firstWhere('material', $item['material']);
            $cantidad = $matData['cantidad_requerida'] ?? 0;
        @endphp
        <tr>
            <td>{{ $item['material'] }}</td>
            <td>{{ (int)$cantidad }}</td>
            <td>{{ (int)$item['palets'] }}</td>
            <td></td>
        </tr>
        @endforeach
        <tr class="resaltar">
            <td>TOTAL {{ $nombre }}</td>
            <td></td>
            <td>{{ (int)$totalPalets }}</td>
            <td></td>
        </tr>
    </table>
    @endforeach


</body>
</html>
