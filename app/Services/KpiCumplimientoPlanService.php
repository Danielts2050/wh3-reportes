<?php

namespace App\Services;

use Carbon\Carbon;

class KpiCumplimientoPlanService
{
    private const MESES = [
        'enero' => 1,
        'febrero' => 2,
        'marzo' => 3,
        'abril' => 4,
        'mayo' => 5,
        'junio' => 6,
        'julio' => 7,
        'agosto' => 8,
        'septiembre' => 9,
        'setiembre' => 9,
        'octubre' => 10,
        'noviembre' => 11,
        'diciembre' => 12,
    ];

    public function procesar(string $datos): array
    {
        \Carbon\Carbon::setLocale('es');

        $items = $this->parsearDatos($datos);
        $this->resultados = $this->clasificarItems($items);

        $metricasGlobales = $this->calcularMetricas($this->resultados);

        $semanas = $this->agruparSemanas($this->resultados);

        $itemsSinStock = array_values(array_filter(
            $this->resultados,
            fn($i) => $i['estado_original'] === '0' || $i['estado_original'] === '1'
        ));

        return array_merge($metricasGlobales, [
            'semanas' => $semanas,
            'items_sin_stock' => $itemsSinStock,
        ]);
    }

    private array $resultados = [];

    private function parsearDatos(string $texto): array
    {
        $lineas = preg_split('/\r\n|\r|\n/', trim($texto));
        $items = [];

        foreach ($lineas as $index => $linea) {
            if ($index === 0) {
                continue;
            }

            if (trim($linea) === '') {
                continue;
            }

            $celdas = explode("\t", $linea);

            if (count($celdas) < 6) {
                continue;
            }

            $cantidadRequerida = $this->normalizarNumero($celdas[3] ?? '');
            $cantidadEnviada = $this->normalizarNumero($celdas[4] ?? '');
            $estadoOriginal = trim($celdas[6] ?? '');
            $diferencia = $cantidadRequerida - $cantidadEnviada;

            $fechaTexto = trim($celdas[2] ?? '');
            $fecha = $this->parsearFecha($fechaTexto);

            $items[] = [
                'codigo' => trim($celdas[0] ?? ''),
                'descripcion' => trim($celdas[1] ?? ''),
                'fecha_plan' => $fechaTexto,
                'fecha' => $fecha,
                'fecha_str' => $fecha ? $fecha->translatedFormat('l j/m') : $fechaTexto,
                'iso' => $fecha ? $fecha->format('Y-m-d') : null,
                'cantidad_requerida' => $cantidadRequerida,
                'cantidad_enviada' => $cantidadEnviada,
                'diferencia' => $diferencia,
                'estado_original' => $estadoOriginal !== '' ? $estadoOriginal : null,
            ];
        }

        return $items;
    }

    public function parsearFecha(string $texto): ?Carbon
    {
        $texto = trim($texto);
        if ($texto === '') {
            return null;
        }

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $texto, $m)) {
            return Carbon::create($m[1], $m[2], $m[3], 0, 0, 0);
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/', $texto, $m)) {
            $anio = strlen($m[3]) === 2 ? 2000 + (int) $m[3] : (int) $m[3];
            return Carbon::create($anio, $m[2], $m[1], 0, 0, 0);
        }

        $limpio = str_replace('de ', ' ', $texto);
        $limpio = str_replace(',', ' ', $limpio);
        $limpio = str_replace('del ', ' ', $limpio);

        if (preg_match('/(\d{1,2})\s+([a-záéíóúñ]+)\s+(\d{4})/i', $limpio, $m)) {
            $dia = (int) $m[1];
            $mes = $this->nombreAMes($m[2]);
            $anio = (int) $m[3];
            if ($mes !== null) {
                return Carbon::create($anio, $mes, $dia, 0, 0, 0);
            }
        }

        try {
            $fecha = Carbon::parse($texto);
            if ($fecha->year > 1970) {
                return $fecha;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    private function nombreAMes(string $nombre): ?int
    {
        $nombre = strtolower(trim($nombre));
        $sinTilde = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $nombre);
        return self::MESES[$sinTilde] ?? null;
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

    private function clasificarItems(array $items): array
    {
        $resultados = [];

        foreach ($items as $item) {
            $clasificacion = $this->clasificarItem($item);

            $item['clasificacion_kpi'] = $clasificacion['categoria'];
            $item['afecta_kpi'] = $clasificacion['afecta_kpi'];
            $item['comentario_causa'] = $clasificacion['comentario'];

            $resultados[] = $item;
        }

        return $resultados;
    }

    private function clasificarItem(array $item): array
    {
        $requerida = $item['cantidad_requerida'];
        $enviada = $item['cantidad_enviada'];
        $estado = $item['estado_original'];

        if ($estado === '0') {
            return [
                'categoria' => 'Fuera de Inventario',
                'afecta_kpi' => 'No',
                'comentario' => 'No había inventario disponible',
            ];
        }

        if ($estado === '1') {
            return [
                'categoria' => 'Agotado',
                'afecta_kpi' => 'No',
                'comentario' => 'Se agotó durante el envío',
            ];
        }

        if ($enviada > $requerida) {
            return [
                'categoria' => 'Enviado de más',
                'afecta_kpi' => 'No',
                'comentario' => 'Se envió más de lo requerido',
            ];
        }

        if ($enviada === $requerida) {
            return [
                'categoria' => 'Completado',
                'afecta_kpi' => 'Si',
                'comentario' => 'Enviado completo',
            ];
        }

        if ($enviada < $requerida) {
            return [
                'categoria' => 'Incompleto Operativo',
                'afecta_kpi' => 'Si',
                'comentario' => 'Cantidad incompleta sin inconveniente de inventario',
            ];
        }

        return [
            'categoria' => 'Sin clasificación',
            'afecta_kpi' => 'No',
            'comentario' => '',
        ];
    }

    private function calcularMetricas(array $items): array
    {
        $totalItemsPlan = count($items);
        $totalRequerido = array_sum(array_column($items, 'cantidad_requerida'));
        $totalEnviado = array_sum(array_column($items, 'cantidad_enviada'));

        $totalCompletadosExactos = count(array_filter($items, fn($i) => $i['clasificacion_kpi'] === 'Completado'));
        $totalIncompletosOperativos = count(array_filter($items, fn($i) => $i['clasificacion_kpi'] === 'Incompleto Operativo'));
        $totalEnviadosDeMas = count(array_filter($items, fn($i) => $i['clasificacion_kpi'] === 'Enviado de más'));
        $totalFueraInventario = count(array_filter($items, fn($i) => $i['clasificacion_kpi'] === 'Fuera de Inventario'));
        $totalAgotados = count(array_filter($items, fn($i) => $i['clasificacion_kpi'] === 'Agotado'));

        $requeridoValido = 0;
        $enviadoEfectivo = 0;
        foreach ($items as $item) {
            if ($item['estado_original'] === null) {
                $requeridoValido += $item['cantidad_requerida'];
                $enviadoEfectivo += min($item['cantidad_enviada'], $item['cantidad_requerida']);
            }
        }

        $cumplimientoPlanPorcentaje = $requeridoValido > 0
            ? round(($enviadoEfectivo / $requeridoValido) * 100, 2)
            : 0;

        $cumplimientoBrutoPorcentaje = $totalRequerido > 0
            ? round(($totalEnviado / $totalRequerido) * 100, 2)
            : 0;

        $requeridoTrabajable = 0;
        $enviadoTrabajable = 0;
        foreach ($items as $item) {
            if ($item['estado_original'] === null) {
                $requeridoTrabajable += $item['cantidad_requerida'];
                $enviadoTrabajable += $item['cantidad_enviada'];
            }
        }

        $cumplimientoOperativoPorcentaje = $requeridoTrabajable > 0
            ? round(($enviadoTrabajable / $requeridoTrabajable) * 100, 2)
            : 0;

        $requeridoAfectadoInventario = 0;
        foreach ($items as $item) {
            if ($item['estado_original'] === '0' || $item['estado_original'] === '1') {
                $requeridoAfectadoInventario += $item['cantidad_requerida'];
            }
        }

        $porcentajeImpactoInventario = $totalRequerido > 0
            ? round(($requeridoAfectadoInventario / $totalRequerido) * 100, 2)
            : 0;

        return [
            'items' => $items,
            'total_items_plan' => $totalItemsPlan,
            'total_requerido' => $totalRequerido,
            'total_enviado' => $totalEnviado,
            'diferencia_total' => $totalRequerido - $totalEnviado,
            'total_completados_exactos' => $totalCompletadosExactos,
            'total_incompletos_operativos' => $totalIncompletosOperativos,
            'total_enviados_de_mas' => $totalEnviadosDeMas,
            'total_fuera_inventario' => $totalFueraInventario,
            'total_agotados' => $totalAgotados,
            'cumplimiento_plan_porcentaje' => $cumplimientoPlanPorcentaje,
            'cumplimiento_bruto_porcentaje' => $cumplimientoBrutoPorcentaje,
            'cumplimiento_operativo_porcentaje' => $cumplimientoOperativoPorcentaje,
            'requerido_trabajable' => $requeridoTrabajable,
            'enviado_trabajable' => $enviadoTrabajable,
            'requerido_afectado_inventario' => $requeridoAfectadoInventario,
            'porcentaje_impacto_inventario' => $porcentajeImpactoInventario,
            'items_trabajables' => $totalCompletadosExactos + $totalIncompletosOperativos + $totalEnviadosDeMas,
            'requerido_valido' => $requeridoValido,
            'enviado_efectivo' => $enviadoEfectivo,
        ];
    }

    private function agruparSemanas(array $items): array
    {
        $conFecha = array_values(array_filter($items, fn($i) => $i['fecha'] !== null));
        usort($conFecha, fn($a, $b) => $a['fecha']->format('Y-m-d') <=> $b['fecha']->format('Y-m-d'));

        $grupos = [];
        foreach ($conFecha as $item) {
            $iso = $item['iso'];
            if (!isset($grupos[$iso])) {
                $grupos[$iso] = [];
            }
            $grupos[$iso][] = $item;
        }

        $semanas = [];
        $semanaActual = [];
        $fechaAnterior = null;

        foreach ($grupos as $iso => $itemsDia) {
            $fecha = Carbon::parse($iso);
            $diasSalto = $fechaAnterior !== null
                ? abs($fecha->getTimestamp() - $fechaAnterior->getTimestamp()) / 86400
                : 0;
            $salto = $diasSalto > 1;

            if ($salto && count($semanaActual) > 0) {
                $semanas[] = $this->armarSemana($semanaActual);
                $semanaActual = [];
            }

            $semanaActual[$iso] = $itemsDia;
            $fechaAnterior = $fecha;
        }

        if (count($semanaActual) > 0) {
            $semanas[] = $this->armarSemana($semanaActual);
        }

        return $semanas;
    }

    private function armarSemana(array $diasAgrupados): array
    {
        $todosItems = array_merge(...array_values($diasAgrupados));

        $dias = [];
        foreach ($diasAgrupados as $iso => $itemsDia) {
            $metricasDia = $this->calcularMetricas($itemsDia);
            $dias[] = [
                'iso' => $iso,
                'fecha' => Carbon::parse($iso),
                'fecha_str' => Carbon::parse($iso)->translatedFormat('l j/m'),
                'items' => $itemsDia,
                'total_items' => $metricasDia['total_items_plan'],
                'requerido' => $metricasDia['total_requerido'],
                'enviado' => $metricasDia['total_enviado'],
                'requerido_valido' => $metricasDia['requerido_valido'],
                'enviado_efectivo' => $metricasDia['enviado_efectivo'],
                'cumplimiento_plan_porcentaje' => $metricasDia['cumplimiento_plan_porcentaje'],
            ];
        }

        return [
            'inicio' => $dias[0]['fecha']->format('Y-m-d'),
            'fin' => $dias[count($dias) - 1]['fecha']->format('Y-m-d'),
            'label' => 'Semana del ' . $dias[0]['fecha']->translatedFormat('d/m')
                . ' al ' . $dias[count($dias) - 1]['fecha']->translatedFormat('d/m'),
            'dias' => $dias,
            'metricas' => $this->calcularMetricas($todosItems),
            'total_items' => count($todosItems),
        ];
    }
}
