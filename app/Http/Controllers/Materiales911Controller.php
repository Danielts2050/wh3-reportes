<?php

namespace App\Http\Controllers;

use App\Services\Materiales911Service;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class Materiales911Controller extends Controller
{
    private Materiales911Service $service;

    public function __construct(Materiales911Service $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('materiales-911.index', [
            'datos' => '',
            'resultado' => null,
        ]);
    }

    public function procesar(Request $request)
    {
        $request->validate([
            'datos' => 'required|string',
        ]);

        $datos = $request->input('datos');
        $resultado = $this->service->procesar($datos);

        return view('materiales-911.index', [
            'datos' => $datos,
            'resultado' => $resultado,
        ]);
    }

    public function exportarPdf(Request $request)
    {
        try {
            $request->validate([
                'datos' => 'required|string',
            ]);

            $datos = $request->input('datos');
            $resultado = $this->service->procesar($datos);

            $pdf = Pdf::loadView('materiales-911.pdf', [
                'resultado' => $resultado,
                'datos' => $datos,
                'fecha_generacion' => now()->format('d/m/Y H:i'),
            ])->setPaper('letter', 'portrait');

            return $pdf->download('materiales-911.pdf');
        } catch (\Throwable $e) {
            return response("Error al generar PDF:\n" . $e->getMessage() . "\n\n" . $e->getTraceAsString(), 500)
                ->header('Content-Type', 'text/plain');
        }
    }
}