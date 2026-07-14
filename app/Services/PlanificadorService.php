<?php

namespace App\Services;

class PlanificadorService
{
    public function procesar(string $planCliente, string|array $stock): array
    {
        $plan = $this->parsearPlan($planCliente);

        if (is_array($stock)) {
            $inventario = $this->parsearStockDesdeExcel($stock);
        } else {
            $inventario = $this->parsearStock($stock);
        }

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

    public function parsearStockDesdeExcel(array $rows): array
    {
        if (empty($rows)) return [];

        $headers = array_map('trim', $rows[0]);
        $colMap = $this->detectarColumnas($headers);

        if (!isset($colMap['material']) || !isset($colMap['total_qty'])) {
            throw new \InvalidArgumentException(
                'No se pudieron detectar las columnas necesarias en el Excel. ' .
                'Asegúrate de que la primera fila contenga encabezados como: Material, Total Qty, Qty/Pallet, Total Pallets, Blocked.'
            );
        }

        $items = [];
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty($row)) continue;

            $material = trim((string)($row[$colMap['material']] ?? ''));
            if ($material === '') continue;

            $totalQty = $this->normalizarNumero((string)($row[$colMap['total_qty']] ?? '0'));
            $qtyPerPallet = $this->normalizarNumero((string)($row[$colMap['qty_per_pallet']] ?? '0'));
            $totalPallets = $this->normalizarNumero((string)($row[$colMap['total_pallets']] ?? '0'));
            $blocked = $this->normalizarNumero((string)($row[$colMap['blocked']] ?? '0'));

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

    private function detectarColumnas(array $headers): array
    {
        $map = [
            'material' => ['material', 'codigo', 'código', 'item', 'article', 'producto', 'parte', 'part number', 'sku'],
            'total_qty' => ['total qty', 'total quantity', 'cantidad total', 'cantidad', 'qty', 'quantity', 'total'],
            'qty_per_pallet' => ['qty/pallet', 'qty per pallet', 'pallet qty', 'cantidad por palet', 'qty x palet', 'qtyxpalet', 'und/palet'],
            'total_pallets' => ['total pallets', 'pallets', 'palets', 'total palets', 'num palets', 'no. palets', 'pallet count'],
            'blocked' => ['blocked', 'bloqueado', 'block', 'inventario bloqueado', 'stock bloqueado', 'reservado'],
        ];

        $result = [];
        foreach ($headers as $i => $header) {
            $headerLower = strtolower(trim($header));
            foreach ($map as $key => $keywords) {
                if (!isset($result[$key])) {
                    foreach ($keywords as $kw) {
                        if (str_contains($headerLower, $kw)) {
                            $result[$key] = $i;
                            break;
                        }
                    }
                }
            }
        }

        return $result;
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

            $paletsRequeridos = $qtyPerPallet > 0 ? ceil($reqQty / $qtyPerPallet) : 0;
            $paletsTotales = $qtyPerPallet > 0 ? $totalQty / $qtyPerPallet : 0;
            $paletsBloqueados = $qtyPerPallet > 0 ? ceil($blocked / $qtyPerPallet) : 0;
            $paletsDisponibles = $qtyPerPallet > 0 ? floor(($totalQty - $blocked) / $qtyPerPallet) : 0;

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
