<?php

namespace App\Services;

class PlanificadorService
{
    public function procesar(string $planCliente, string $stock): array
    {
        $plan = $this->parsearPlan($planCliente);
        $inventario = $this->parsearStock($stock);

        $materiales = $this->cruzarDatos($plan, $inventario);

        return [
            'materiales' => $materiales,
            'total_materiales' => count($materiales),
            'total_palets_requeridos' => array_sum(array_column($materiales, 'palets_requeridos')),
            'total_palets_disponibles' => array_sum(array_column($materiales, 'palets_disponibles')),
            'total_materiales_con_stock' => count(array_filter($materiales, fn($m) => $m['palets_disponibles'] > 0)),
            'total_materiales_agotados' => count(array_filter($materiales, fn($m) => $m['palets_disponibles'] <= 0)),
        ];
    }

    private function parsearPlan(string $texto): array
    {
        $lineas = preg_split('/\r\n|\r|\n/', trim($texto));
        $items = [];

        foreach ($lineas as $linea) {
            if (trim($linea) === '') continue;

            $celdas = explode("\t", $linea);
            if (count($celdas) < 2) continue;

            $material = trim($celdas[0]);
            $cantidad = $this->normalizarNumero($celdas[1] ?? '0');

            if ($material === '' || $cantidad <= 0) continue;

            $items[] = [
                'material' => $material,
                'cantidad_requerida' => $cantidad,
            ];
        }

        return $items;
    }

    private function parsearStock(string $texto): array
    {
        $lineas = preg_split('/\r\n|\r|\n/', trim($texto));
        $items = [];

        foreach ($lineas as $index => $linea) {
            if ($index === 0) continue;
            if (trim($linea) === '') continue;

            $celdas = explode("\t", $linea);
            if (count($celdas) < 5) continue;

            $material = trim($celdas[0]);
            $totalQty = $this->normalizarNumero($celdas[1] ?? '0');
            $qtyPerPallet = $this->normalizarNumero($celdas[2] ?? '0');
            $totalPallets = $this->normalizarNumero($celdas[3] ?? '0');
            $blocked = $this->normalizarNumero($celdas[4] ?? '0');

            if ($material === '') continue;

            $items[$material] = [
                'material' => $material,
                'total_qty' => $totalQty,
                'qty_per_pallet' => $qtyPerPallet,
                'total_pallets' => $totalPallets,
                'blocked' => $blocked,
            ];
        }

        return $items;
    }

    private function cruzarDatos(array $plan, array $inventario): array
    {
        $resultados = [];

        foreach ($plan as $item) {
            $material = $item['material'];
            $reqQty = $item['cantidad_requerida'];
            $stock = $inventario[$material] ?? null;

            $qtyPerPallet = $stock ? $stock['qty_per_pallet'] : 0;
            $totalQty = $stock ? $stock['total_qty'] : 0;
            $blocked = $stock ? $stock['blocked'] : 0;

            $paletsRequeridos = $qtyPerPallet > 0 ? $reqQty / $qtyPerPallet : 0;
            $paletsTotales = $qtyPerPallet > 0 ? $totalQty / $qtyPerPallet : 0;
            $paletsBloqueados = $qtyPerPallet > 0 ? $blocked / $qtyPerPallet : 0;
            $paletsDisponibles = $paletsTotales - $paletsBloqueados;

            $cantidadDisponible = $totalQty - $blocked;

            if ($paletsDisponibles <= 0 && $paletsRequeridos > 0) {
                $estado = 'agotado';
            } elseif ($paletsDisponibles >= $paletsRequeridos) {
                $estado = 'disponible';
            } else {
                $estado = 'parcial';
            }

            $resultados[] = [
                'material' => $material,
                'cantidad_requerida' => $reqQty,
                'qty_per_pallet' => $qtyPerPallet,
                'cantidad_disponible' => $cantidadDisponible,
                'palets_requeridos' => round($paletsRequeridos, 3),
                'palets_totales' => round($paletsTotales, 3),
                'palets_bloqueados' => round($paletsBloqueados, 3),
                'palets_disponibles' => round($paletsDisponibles, 3),
                'palets_asignados' => 0,
                'estado' => $estado,
            ];
        }

        return $resultados;
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
}
