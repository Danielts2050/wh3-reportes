<?php

namespace App\Http\Controllers;

use App\Services\KpiCumplimientoPlanService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class KpiCumplimientoPlanController extends Controller
{
    private KpiCumplimientoPlanService $kpiService;

    public function __construct(KpiCumplimientoPlanService $kpiService)
    {
        $this->kpiService = $kpiService;
    }

    public function index()
    {
        return view('kpi-cumplimiento-plan.index', [
            'resultado' => null,
            'datos' => '',
        ]);
    }

    public function procesar(Request $request)
    {
        $request->validate([
            'datos' => 'required|string',
        ]);

        $datos = $request->input('datos');

        $metricas = $this->kpiService->procesar($datos);

        $observacionesGenerales = $this->generarObservaciones($metricas);

        return view('kpi-cumplimiento-plan.dashboard', array_merge($metricas, [
            'datos' => $datos,
            'observaciones_generales' => $observacionesGenerales,
        ]));
    }

    public function exportarPdf(Request $request)
    {
        try {
            $request->validate([
                'datos' => 'required|string',
            ]);

            $datos = $request->input('datos');

            $metricas = $this->kpiService->procesar($datos);

            $observacionesGenerales = $this->generarObservaciones($metricas);

            $pdfData = array_merge($metricas, [
                'fecha_generacion' => now()->format('d/m/Y H:i'),
                'observaciones_generales' => $observacionesGenerales,
            ]);

            $pdf = Pdf::loadView('kpi-cumplimiento-plan.pdf', $pdfData)
                ->setPaper('letter', 'portrait');

            $semanas = $metricas['semanas'] ?? [];
            $nombre = 'reporte-kpi';
            if (count($semanas) > 0) {
                $nombre .= '-' . $semanas[0]['inicio'] . '-' . $semanas[count($semanas) - 1]['fin'];
            }

            return $pdf->download($nombre . '.pdf');
        } catch (\Throwable $e) {
            return response("Error al generar PDF:\n" . $e->getMessage() . "\n\n" . $e->getTraceAsString(), 500)
                ->header('Content-Type', 'text/plain');
        }
    }

    private function generarObservaciones(array $metricas): array
    {
        $observaciones = [];

        if ($metricas['porcentaje_impacto_inventario'] > 0) {
            $observaciones[] = 'Se recomienda analizar la disponibilidad de inventario para reducir el impacto en futuros planes. '
                . 'El ' . number_format($metricas['porcentaje_impacto_inventario'], 2) . '% del requerido total no pudo ser despachado por falta de stock.';
        }

        $diasFaltantes = [];
        foreach ($metricas['semanas'] as $semana) {
            foreach ($semana['dias'] as $dia) {
                if ($dia['cumplimiento_plan_porcentaje'] < 100) {
                    $diasFaltantes[] = $dia['fecha_str'] . ' (' . number_format($dia['cumplimiento_plan_porcentaje'], 2) . '%)';
                }
            }
        }

        if (count($diasFaltantes) > 0) {
            $observaciones[] = 'Días sin cumplimiento total del plan: ' . implode(', ', $diasFaltantes) . '.';
        }

        return $observaciones;
    }
}