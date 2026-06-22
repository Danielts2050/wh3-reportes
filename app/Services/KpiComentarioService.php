<?php

namespace App\Services;

class KpiComentarioService
{
    public function generar(array $metricas, int $contenedoresCompletados, int $contenedoresPlanificados = 8): string
    {
        $capacidadOperativa = $contenedoresPlanificados > 0
            ? round(($contenedoresCompletados / $contenedoresPlanificados) * 100, 2)
            : 0;

        if ($contenedoresCompletados >= $contenedoresPlanificados) {
            return "Se alcanzó el objetivo diario de {$contenedoresPlanificados} contenedores. "
                 . "El plan se completó al {$capacidadOperativa}% en capacidad operativa.";
        }

        return "No se alcanzó el objetivo de {$contenedoresPlanificados} contenedores. "
             . "Se completaron {$contenedoresCompletados} contenedores ({$capacidadOperativa}%).";
    }
}
