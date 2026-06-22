<?php

namespace App\Services;

class KpiCumplimientoPlanService
{
    private array $resultados = [];

    public function procesar(string $datos): array
    {
        $items = $this->parsearDatos($datos);
        $this->resultados = $this->clasificarItems($items);

        return $this->calcularMetricas();
    }

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

            $items[] = [
                'codigo' => trim($celdas[0] ?? ''),
                'descripcion' => trim($celdas[1] ?? ''),
                'fecha_plan' => trim($celdas[2] ?? ''),
                'cantidad_requerida' => $cantidadRequerida,
                'cantidad_enviada' => $cantidadEnviada,
                'diferencia' => $diferencia,
                'estado_original' => $estadoOriginal !== '' ? $estadoOriginal : null,
            ];
        }

        return $items;
    }

    private function normalizarNumero(string $valor): float
    {
        $valor = trim($valor);
        $valor = str_replace(',', '', $valor);
        $valor = str_replace('.00', '', $valor);

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

    private function calcularMetricas(): array
    {
        $items = $this->resultados;

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
            'items' => $this->resultados,
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
}
