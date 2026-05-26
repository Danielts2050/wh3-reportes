<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DistribucionLotesController extends Controller
{
    public function index()
    {
        return view('distribucion-lotes.index', [
            'datos' => '',
            'lotes' => [],
            'general' => [],
        ]);
    }

    public function procesar(Request $request)
    {
        $request->validate([
            'datos' => 'required|string'
        ]);

        $datos = $request->input('datos');
        $filas = $this->parsearDatos($datos);

        $maxLotePorMaterial = [];

        foreach ($filas as $fila) {
            if ($fila['N.Lotes'] === '') {
                continue;
            }

            $n = intval($fila['N.Lotes']);
            $mat = $fila['Material'];

            if (!isset($maxLotePorMaterial[$mat]) || $n > $maxLotePorMaterial[$mat]) {
                $maxLotePorMaterial[$mat] = $n;
            }
        }

        $lotes = [];
        $general = [];

        foreach ($filas as $fila) {
            $mat = $fila['Material'];

            if ($fila['N.Lotes'] === '') {
                $general[] = $fila;
                continue;
            }

            $n = intval($fila['N.Lotes']);

            if (isset($maxLotePorMaterial[$mat]) && $n === $maxLotePorMaterial[$mat]) {
                $general[] = $fila;
            } else {
                $lotes[$n][] = $fila;
            }
        }

        ksort($lotes);

        usort($general, fn($a, $b) => strcmp($a['localidad'], $b['localidad']));

        return view('distribucion-lotes.index', [
            'datos' => $datos,
            'lotes' => $lotes,
            'general' => $general,
        ]);
    }

    private function parsearDatos(string $texto): array
    {
        $lineas = preg_split('/\r\n|\r|\n/', trim($texto));

        $filas = [];

        foreach ($lineas as $index => $linea) {
            if ($index === 0) {
                continue;
            }

            if (trim($linea) === '') {
                continue;
            }

            $celdas = explode("\t", $linea);

            if (count($celdas) < 5) {
                continue;
            }

            $filas[] = [
                'Material' => trim($celdas[0]),
                'Lotes' => trim($celdas[1]),
                'localidad' => trim($celdas[2]),
                'Cantidad' => trim($celdas[3]),
                'N.Lotes' => trim($celdas[4]),
            ];
        }

        return $filas;
    }

    public function exportar(Request $request)
{
    $lotes = json_decode($request->lotes, true);
    $general = json_decode($request->general, true);

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
    <table cellspacing="0" cellpadding="10" style="$borde = border:1px solid #000; padding:8px 14px;; font-family:Arial; font-size:11pt;">
        <tr>
            <th style="'.$borde.$amarillo.$centro.$negrita.'">Material</th>
            <th style="'.$borde.$amarillo.$centro.$negrita.'">Lotes</th>
            <th style="'.$borde.$amarillo.$centro.$negrita.'">localidad</th>
            <th style="'.$borde.$amarillo.$centro.$negrita.'">Cantidad</th>
            <th style="'.$borde.$amarillo.$centro.$negrita.'">N.Lotes</th>
        </tr>
    ';

    foreach ($lotes as $numero => $filas) {
        $html .= '
        <tr>
            <td colspan="5" style="'.$borde.$negrita.$centro.'">Lote '.$numero.'</td>
        </tr>';

        foreach ($filas as $fila) {
            $html .= $this->filaExcel($fila, $borde, $centro);
        }

        $html .= '<tr><td colspan="5">&nbsp;</td></tr>';
    }

    if (!empty($general)) {
        $html .= '
        <tr>
            <td colspan="5" style="'.$borde.$negrita.$centro.'">General</td>
        </tr>';

        foreach ($general as $fila) {
            $html .= $this->filaExcel($fila, $borde, $centro);
        }
    }

    $html .= '
    </table>
    </body>
    </html>';

    return response($html)
        ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
        ->header('Content-Disposition', 'attachment; filename="distribucion_lotes.xls"');
}

private function filaExcel($fila, $borde, $centro)
{
    return '
    <tr>
        <td style="'.$borde.$centro.'">'.$fila['Material'].'</td>
        <td style="'.$borde.$centro.'">'.$fila['Lotes'].'</td>
        <td style="'.$borde.$centro.'">'.$fila['localidad'].'</td>
        <td style="'.$borde.$centro.'">'.$fila['Cantidad'].'</td>
        <td style="'.$borde.$centro.'">'.$fila['N.Lotes'].'</td>
    </tr>';
}

    private function limpiarNumero($valor)
    {
        $valor = str_replace(',', '.', trim($valor));

        if (substr_count($valor, '.') > 1) {
            $valor = str_replace('.', '', $valor);
        }

        return is_numeric($valor) ? (float) $valor : 0;
    }
}