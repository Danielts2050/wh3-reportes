<?php

namespace App\Services;

use Carbon\Carbon;

class Materiales911Service
{
    private const HEADER_KEYWORDS = ['material', 'status', 'fecha', 'cantidad'];

    public function procesar(string $datos): array
    {
        $items = $this->parsearDatos($datos);

        return [
            'items' => $items,
            'totales' => $this->calcularTotales($items),
            'por_dia' => $this->agruparPorDia($items),
            'por_solicitante' => $this->agruparPorCampo($items, 'solicitante', true),
            'por_via' => $this->agruparPorCampo($items, 'via'),
            'por_status' => $this->agruparPorStatus($items),
            'por_planificado' => $this->agruparSiNo($items, 'planificado'),
            'por_prioridad' => $this->agruparSiNo($items, 'prioridad'),
        ];
    }

    private function parsearDatos(string $texto): array
    {
        $lineas = preg_split('/\r\n|\r|\n/', trim($texto));
        $items = [];

        foreach ($lineas as $index => $linea) {
            if (trim($linea) === '') {
                continue;
            }

            $celdas = explode("\t", $linea);

            if (count($celdas) < 7) {
                continue;
            }

            if ($index === 0 && $this->esEncabezado($celdas)) {
                continue;
            }

            $fecha = $this->parsearFecha(trim($celdas[1] ?? ''));
            $statusRaw = trim($celdas[8] ?? '');
            $statusInfo = $this->normalizarStatus($statusRaw);

            $items[] = [
                'material' => trim($celdas[0] ?? ''),
                'fecha' => $fecha,
                'fecha_str' => $fecha ? $fecha->format('d/m/Y') : trim($celdas[1] ?? ''),
                'iso' => $fecha ? $fecha->format('Y-m-d') : null,
                'cantidad' => $this->normalizarNumero($celdas[2] ?? ''),
                'paletas' => (int) round($this->normalizarNumero($celdas[3] ?? '')),
                'prioridad' => strtoupper(trim($celdas[4] ?? '')),
                'planificado' => strtoupper(trim($celdas[5] ?? '')),
                'via' => trim($celdas[6] ?? ''),
                'solicitante' => trim($celdas[7] ?? ''),
                'status_raw' => $statusRaw,
                'status_key' => $statusInfo['key'],
                'status_label' => $statusInfo['label'],
            ];
        }

        return $items;
    }

    private function esEncabezado(array $celdas): bool
    {
        $primera = strtolower(trim($celdas[0] ?? ''));
        foreach (self::HEADER_KEYWORDS as $kw) {
            if ($primera === $kw) {
                return true;
            }
        }

        $texto = strtolower(implode(' ', array_slice($celdas, 0, 9)));
        return str_contains($texto, 'status') && str_contains($texto, 'material');
    }

    private function parsearFecha(string $texto): ?Carbon
    {
        $texto = trim($texto);
        if ($texto === '') {
            return null;
        }

        $limpio = str_replace('.', '/', $texto);

        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{2,4})$#', $limpio, $m)) {
            $anio = strlen($m[3]) === 2 ? 2000 + (int) $m[3] : (int) $m[3];
            return Carbon::create($anio, (int) $m[2], (int) $m[1], 0, 0, 0);
        }

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $texto, $m)) {
            return Carbon::create((int) $m[1], (int) $m[2], (int) $m[3], 0, 0, 0);
        }

        try {
            $fecha = Carbon::parse($texto);
            return $fecha->year > 1970 ? $fecha : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function normalizarNumero(string $valor): float
    {
        $valor = trim($valor);
        if ($valor === '') return 0;

        $ultimoPunto = strrpos($valor, '.');
        $ultimaComa = strrpos($valor, ',');

        if ($ultimoPunto !== false && $ultimaComa !== false) {
            if ($ultimaComa > $ultimoPunto) {
                $valor = str_replace('.', '', $valor);
                $valor = str_replace(',', '.', $valor);
            } else {
                $valor = str_replace(',', '', $valor);
            }
        } elseif ($ultimoPunto !== false) {
            if (strlen(substr($valor, $ultimoPunto + 1)) === 3) {
                $valor = str_replace('.', '', $valor);
            }
        } elseif ($ultimaComa !== false) {
            $despues = substr($valor, $ultimaComa + 1);
            if (strlen($despues) <= 2 && is_numeric($despues)) {
                $valor = str_replace(',', '.', $valor);
            } else {
                $valor = str_replace(',', '', $valor);
            }
        }

        return is_numeric($valor) ? (float) $valor : 0;
    }

    private function normalizarStatus(string $raw): array
    {
        $raw = trim($raw);
        $lower = strtolower($raw);

        if ($lower === 'ok') {
            return ['key' => 'OK', 'label' => 'OK'];
        }

        if ($lower === 'q' || str_contains($lower, 'status q') || str_contains($lower, 'calidad')) {
            return ['key' => 'Q', 'label' => 'Q – En proceso de calidad'];
        }

        if ($raw === '') {
            return ['key' => '', 'label' => 'Sin status'];
        }

        return ['key' => $raw, 'label' => $raw];
    }

    private function calcularTotales(array $items): array
    {
        return [
            'registros' => count($items),
            'materiales_unicos' => count(array_unique(array_column($items, 'material'))),
            'total_cantidad' => array_sum(array_column($items, 'cantidad')),
            'total_paletas' => array_sum(array_column($items, 'paletas')),
            'dias' => count(array_unique(array_filter(array_column($items, 'iso')))),
        ];
    }

    private function agruparPorDia(array $items): array
    {
        $grupos = [];

        foreach ($items as $item) {
            $key = $item['iso'] ?? ('na-' . $item['fecha_str']);
            $etiqueta = $item['fecha_str'];

            if (!isset($grupos[$key])) {
                $grupos[$key] = [
                    'fecha_str' => $etiqueta,
                    'cantidad' => 0,
                    'paletas' => 0,
                    'registros' => 0,
                ];
            }

            $grupos[$key]['cantidad'] += $item['cantidad'];
            $grupos[$key]['paletas'] += $item['paletas'];
            $grupos[$key]['registros']++;
        }

        $resultado = array_values($grupos);

        usort($resultado, function ($a, $b) {
            $fa = \DateTime::createFromFormat('d/m/Y', $a['fecha_str']);
            $fb = \DateTime::createFromFormat('d/m/Y', $b['fecha_str']);
            if ($fa && $fb) {
                return $fa <=> $fb;
            }
            return strcmp($a['fecha_str'], $b['fecha_str']);
        });

        return $resultado;
    }

    private function agruparPorCampo(array $items, string $campo, bool $ordenarPorCantidad = false): array
    {
        $grupos = [];

        foreach ($items as $item) {
            $valor = $item[$campo] !== '' ? $item[$campo] : 'Sin asignar';

            if (!isset($grupos[$valor])) {
                $grupos[$valor] = [
                    'nombre' => $valor,
                    'cantidad' => 0,
                    'paletas' => 0,
                    'registros' => 0,
                ];
            }

            $grupos[$valor]['cantidad'] += $item['cantidad'];
            $grupos[$valor]['paletas'] += $item['paletas'];
            $grupos[$valor]['registros']++;
        }

        $resultado = array_values($grupos);

        usort($resultado, function ($a, $b) use ($ordenarPorCantidad) {
            if ($ordenarPorCantidad) {
                return $b['cantidad'] <=> $a['cantidad'];
            }
            return strcmp($a['nombre'], $b['nombre']);
        });

        return $resultado;
    }

    private function agruparPorStatus(array $items): array
    {
        $grupos = [];

        foreach ($items as $item) {
            $key = $item['status_key'];

            if (!isset($grupos[$key])) {
                $grupos[$key] = [
                    'status' => $item['status_label'],
                    'registros' => 0,
                    'cantidad' => 0,
                    'paletas' => 0,
                ];
            }

            $grupos[$key]['registros']++;
            $grupos[$key]['cantidad'] += $item['cantidad'];
            $grupos[$key]['paletas'] += $item['paletas'];
        }

        $resultado = array_values($grupos);

        usort($resultado, fn($a, $b) => $b['registros'] <=> $a['registros']);

        return $resultado;
    }

    private function agruparSiNo(array $items, string $campo): array
    {
        $grupos = [];

        foreach ($items as $item) {
            $valor = $item[$campo] === 'SI' ? 'SI' : ($item[$campo] === 'NO' ? 'NO' : 'N/A');

            if (!isset($grupos[$valor])) {
                $grupos[$valor] = ['valor' => $valor, 'registros' => 0, 'cantidad' => 0];
            }

            $grupos[$valor]['registros']++;
            $grupos[$valor]['cantidad'] += $item['cantidad'];
        }

        $total = count($items);
        $resultado = array_values($grupos);

        foreach ($resultado as &$g) {
            $g['porcentaje'] = $total > 0 ? round(($g['registros'] / $total) * 100, 2) : 0;
        }

        return $resultado;
    }
}
