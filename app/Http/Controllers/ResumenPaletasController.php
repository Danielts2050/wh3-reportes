<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResumenPaletasController extends Controller
{
    public function index()
    {
        return view('resumen-paletas.index', [
            'resultado' => null,
            'referencia' => '',
            'datos' => ''
        ]);
    }

    public function procesar(Request $request)
    {
        $request->validate([
            'datos' => 'required|string',
            'referencia' => 'nullable|string'
        ]);

        $datos = $request->input('datos');
        $referencia = $request->input('referencia', '');

        $lineas = preg_split('/\r\n|\r|\n/', trim($datos));

        $resumen = [];

        foreach ($lineas as $linea) {
            if (trim($linea) === '') {
                continue;
            }

            $columnas = explode("\t", $linea);

            if (count($columnas) < 2) {
                continue;
            }

            $material = trim($columnas[0]);
            $cantidad = $this->limpiarNumero($columnas[1]);

            if ($material === '') {
                continue;
            }

            if (!isset($resumen[$material])) {
                $resumen[$material] = [
                    'Material' => $material,
                    'Cantidad' => 0,
                    'Paletas' => 0,
                    'Referencia' => ''
                ];
            }

            $resumen[$material]['Cantidad'] += $cantidad;
            $resumen[$material]['Paletas'] += 1;
        }

        $resultado = array_values($resumen);

        $totalCantidad = array_sum(array_column($resultado, 'Cantidad'));
        $totalPaletas = array_sum(array_column($resultado, 'Paletas'));

        if (count($resultado) > 0) {
            $resultado[0]['Referencia'] = $referencia;
        }

        $resultado[] = [
            'Material' => 'TOTAL',
            'Cantidad' => $totalCantidad,
            'Paletas' => $totalPaletas,
            'Referencia' => ''
        ];

        return view('resumen-paletas.index', [
            'resultado' => $resultado,
            'referencia' => $referencia,
            'datos' => $datos
        ]);
    }

    public function exportar(Request $request)
{
    $resultado = json_decode($request->resultado, true);

    $referencia = '';
    foreach ($resultado as $fila) {
        if (!empty($fila['Referencia'])) {
            $referencia = $fila['Referencia'];
            break;
        }
    }

    $rowspan = count($resultado);

    $borde = 'border:1px solid #000;';
    $amarillo = 'background-color:#FFF200;';
    $centro = 'text-align:center; vertical-align:middle;';
    $negrita = 'font-weight:bold;';

    $html = '
    <html>
    <head>
        <meta charset="UTF-8">
    </head>
    <body>
    <table cellspacing="0" cellpadding="4" style="border-collapse:collapse; font-family:Arial; font-size:11pt;">
        <tr>
            <th style="'.$borde.$amarillo.$centro.$negrita.' width:120px;">Material</th>
            <th style="'.$borde.$amarillo.$centro.$negrita.' width:120px;">Cantidad</th>
            <th style="'.$borde.$amarillo.$centro.$negrita.' width:100px;">Paletas</th>
            <th style="'.$borde.$amarillo.$centro.$negrita.' width:160px;">Referencia</th>
        </tr>
    ';

    $primeraFila = true;

    foreach ($resultado as $fila) {
        $esTotal = $fila['Material'] === 'TOTAL';

        $estiloFila = $esTotal ? $amarillo.$negrita : '';

        $html .= '<tr>';

        $html .= '<td style="'.$borde.$centro.$estiloFila.'">'.$fila['Material'].'</td>';
        $html .= '<td style="'.$borde.$centro.$estiloFila.'">'.number_format($fila['Cantidad'], 0).'</td>';
        $html .= '<td style="'.$borde.$centro.$estiloFila.'">'.$fila['Paletas'].'</td>';

        if ($primeraFila) {
            $html .= '
                <td rowspan="'.$rowspan.'" style="'.$borde.$amarillo.$centro.$negrita.'">
                    '.$referencia.'
                </td>
            ';

            $primeraFila = false;
        }

        $html .= '</tr>';
    }

    $html .= '
    </table>
    </body>
    </html>
    ';

    return response($html)
        ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
        ->header('Content-Disposition', 'attachment; filename="resumen_paletas.xls"');
}

    private function limpiarNumero($valor)
    {
        $valor = trim($valor);
        $valor = str_replace(',', '', $valor);

        return is_numeric($valor) ? (float) $valor : 0;
    }
}