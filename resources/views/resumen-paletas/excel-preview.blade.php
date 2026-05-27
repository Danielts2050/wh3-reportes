<table 
    cellspacing="0" 
    cellpadding="10"
    style="
        border-collapse:collapse;
        font-family:Arial, sans-serif;
        font-size:14px;
        background:#ffffff;
    "
>
    <thead>
        <tr>
            <th style="border:1px solid #000; padding:8px 16px; background:#FFF200; text-align:center; font-weight:bold;">Material</th>
            <th style="border:1px solid #000; padding:8px 16px; background:#FFF200; text-align:center; font-weight:bold;">Cantidad</th>
            <th style="border:1px solid #000; padding:8px 16px; background:#FFF200; text-align:center; font-weight:bold;">Paletas</th>
            <th style="border:1px solid #000; padding:8px 16px; background:#FFF200; text-align:center; font-weight:bold;">Referencia</th>
        </tr>
    </thead>

    <tbody>
        @php
            $referencia = '';
            foreach ($resultado as $fila) {
                if (!empty($fila['Referencia'])) {
                    $referencia = $fila['Referencia'];
                    break;
                }
            }
        @endphp

        @foreach($resultado as $fila)
            <tr>
                <td style="border:1px solid #000; padding:8px 16px; text-align:center; {{ $fila['Material'] === 'TOTAL' ? 'background:#FFF200; font-weight:bold;' : '' }}">
                    {{ $fila['Material'] }}
                </td>

                <td style="border:1px solid #000; padding:8px 16px; text-align:center; {{ $fila['Material'] === 'TOTAL' ? 'background:#FFF200; font-weight:bold;' : '' }}">
                    {{ number_format($fila['Cantidad'], 0) }}
                </td>

                <td style="border:1px solid #000; padding:8px 16px; text-align:center; {{ $fila['Material'] === 'TOTAL' ? 'background:#FFF200; font-weight:bold;' : '' }}">
                    {{ $fila['Paletas'] }}
                </td>

                @if($loop->first)
                    <td 
                        rowspan="{{ count($resultado) }}"
                        style="
                            border:1px solid #000;
                            padding:8px 16px;
                            background:#FFF200;
                            text-align:center;
                            vertical-align:middle;
                            font-weight:bold;
                        "
                    >
                        {{ $referencia }}
                    </td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>