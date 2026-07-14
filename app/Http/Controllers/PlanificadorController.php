<?php

namespace App\Http\Controllers;

use App\Services\PlanificadorService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PlanificadorController extends Controller
{
    private PlanificadorService $planificadorService;

    public function __construct(PlanificadorService $planificadorService)
    {
        $this->planificadorService = $planificadorService;
    }

    public function index()
    {
        return view('planificador.index');
    }

    public function procesar(Request $request)
    {
        $request->validate([
            'plan_cliente' => 'required|string',
            'stock' => 'required',
        ]);

        if ($request->hasFile('stock')) {
            $file = $request->file('stock');
            $spreadsheet = IOFactory::load($file->getPathname());
            $rows = $spreadsheet->getActiveSheet()->toArray();
            $stockData = $rows ?: [];
        } else {
            $stockData = $request->input('stock');
        }

        $resultado = $this->planificadorService->procesar(
            $request->input('plan_cliente'),
            $stockData
        );

        return view('planificador.board', [
            'materiales' => $resultado['materiales'],
            'total_materiales' => $resultado['total_materiales'],
            'total_palets_requeridos' => $resultado['total_palets_requeridos'],
            'total_palets_disponibles' => $resultado['total_palets_disponibles'],
            'total_materiales_con_stock' => $resultado['total_materiales_con_stock'],
            'total_materiales_agotados' => $resultado['total_materiales_agotados'],
        ]);
    }

    public function exportar(Request $request)
    {
        $request->validate([
            'estado_tablero' => 'required|json',
        ]);

        $estado = json_decode($request->input('estado_tablero'), true);

        $html = view('planificador.excel', [
            'estado' => $estado,
        ])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="plan-carga.xls"');
    }
}
