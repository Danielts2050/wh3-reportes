<?php

namespace App\Http\Controllers;

use App\Services\KpiCumplimientoPlanService;
use App\Services\KpiComentarioService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class KpiCumplimientoPlanController extends Controller
{
    private KpiCumplimientoPlanService $kpiService;
    private KpiComentarioService $comentarioService;

    public function __construct(
        KpiCumplimientoPlanService $kpiService,
        KpiComentarioService $comentarioService
    ) {
        $this->kpiService = $kpiService;
        $this->comentarioService = $comentarioService;
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
            'fecha_plan' => 'required|date',
            'contenedores_completados' => 'required|integer|min:0|max:8',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $datos = $request->input('datos');
        $fechaPlan = $request->input('fecha_plan');
        $contenedoresCompletados = (int) $request->input('contenedores_completados');
        $observaciones = $request->input('observaciones', '');

        $metricas = $this->kpiService->procesar($datos);

        $comentarioAutomatico = $this->comentarioService->generar(
            $metricas,
            $contenedoresCompletados
        );

        $contenedoresPlanificados = 8;
        $contenedoresNoCompletados = $contenedoresPlanificados - $contenedoresCompletados;
        $capacidadOperativa = $contenedoresPlanificados > 0
            ? round(($contenedoresCompletados / $contenedoresPlanificados) * 100, 2)
            : 0;

        $estadoDia = $contenedoresCompletados >= $contenedoresPlanificados ? 'Completo' : 'Incompleto';

        $observacionesGenerales = $this->generarObservaciones($metricas, $contenedoresCompletados, $contenedoresPlanificados);

        return view('kpi-cumplimiento-plan.dashboard', array_merge($metricas, [
            'datos' => $datos,
            'fecha_plan' => $fechaPlan,
            'contenedores_planificados' => $contenedoresPlanificados,
            'contenedores_completados' => $contenedoresCompletados,
            'contenedores_no_completados' => $contenedoresNoCompletados,
            'capacidad_operativa' => $capacidadOperativa,
            'estado_dia' => $estadoDia,
            'comentario_automatico' => $comentarioAutomatico,
            'observaciones' => $observaciones,
            'observaciones_generales' => $observacionesGenerales,
        ]));
    }

    public function exportarPdf(Request $request)
    {
        $request->validate([
            'datos' => 'required|string',
            'fecha_plan' => 'required|date',
            'contenedores_completados' => 'required|integer|min:0|max:8',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $datos = $request->input('datos');
        $fechaPlan = $request->input('fecha_plan');
        $contenedoresCompletados = (int) $request->input('contenedores_completados');
        $observaciones = $request->input('observaciones', '');

        $metricas = $this->kpiService->procesar($datos);

        $comentarioAutomatico = $this->comentarioService->generar(
            $metricas,
            $contenedoresCompletados
        );

        $contenedoresPlanificados = 8;
        $contenedoresNoCompletados = $contenedoresPlanificados - $contenedoresCompletados;
        $capacidadOperativa = $contenedoresPlanificados > 0
            ? round(($contenedoresCompletados / $contenedoresPlanificados) * 100, 2)
            : 0;

        $estadoDia = $contenedoresCompletados >= $contenedoresPlanificados ? 'Completo' : 'Incompleto';

        $observacionesGenerales = $this->generarObservaciones($metricas, $contenedoresCompletados, $contenedoresPlanificados);

        $pdfData = array_merge($metricas, [
            'fecha_plan' => $fechaPlan,
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'contenedores_planificados' => $contenedoresPlanificados,
            'contenedores_completados' => $contenedoresCompletados,
            'contenedores_no_completados' => $contenedoresNoCompletados,
            'capacidad_operativa' => $capacidadOperativa,
            'estado_dia' => $estadoDia,
            'comentario_automatico' => $comentarioAutomatico,
            'observaciones' => $observaciones,
            'observaciones_generales' => $observacionesGenerales,
        ]);

        $pdf = Pdf::loadView('kpi-cumplimiento-plan.pdf', $pdfData)
            ->setPaper('letter', 'portrait');

        $nombreArchivo = 'reporte-kpi-' . $fechaPlan . '.pdf';

        return $pdf->download($nombreArchivo);
    }

    private function generarObservaciones(array $metricas, int $completados, int $planificados): array
    {
        $observaciones = [];

        if ($metricas['porcentaje_impacto_inventario'] > 0) {
            $observaciones[] = "Se recomienda analizar la disponibilidad de inventario para reducir el impacto en futuros planes.";
        }

        return $observaciones;
    }
}
