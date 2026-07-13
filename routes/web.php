<?php

use App\Http\Controllers\DistribucionLotesController;
use App\Http\Controllers\HorasExtrasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KpiCumplimientoPlanController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ResumenPaletasController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/resumen-paletas');
});

Route::get('/dashboard', function () {
    return redirect('/resumen-paletas');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Módulos WH3
|--------------------------------------------------------------------------
*/

Route::get('/resumen-paletas', [ResumenPaletasController::class, 'index']);
Route::post('/resumen-paletas/procesar', [ResumenPaletasController::class, 'procesar']);
Route::post('/resumen-paletas/exportar', [ResumenPaletasController::class, 'exportar']);

Route::get('/distribucion-lotes', [DistribucionLotesController::class, 'index']);
Route::post('/distribucion-lotes/procesar', [DistribucionLotesController::class, 'procesar']);
Route::post('/distribucion-lotes/exportar', [DistribucionLotesController::class, 'exportar']);

Route::get('/kpi-cumplimiento-plan', [KpiCumplimientoPlanController::class, 'index']);
Route::post('/kpi-cumplimiento-plan/procesar', [KpiCumplimientoPlanController::class, 'procesar']);
Route::post('/kpi-cumplimiento-plan/exportar-pdf', [KpiCumplimientoPlanController::class, 'exportarPdf']);

Route::get('/test-pdf', function () {
    try {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML('<h1>Test PDF</h1><p>DomPDF funciona correctamente en Render.</p>');
        return $pdf->download('test.pdf');
    } catch (\Throwable $e) {
        return response("Error test-pdf:\n" . $e->getMessage() . "\n\n" . $e->getTraceAsString(), 500)
            ->header('Content-Type', 'text/plain');
    }
});

// rutas de reportes.
// Route::get('/reporte', [ReporteController::class, 'index']);
// Route::post('/reporte/procesar', [ReporteController::class, 'procesar']);

/*
|--------------------------------------------------------------------------
| Horas Extras Protegido por Login
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/horas-extras', [HorasExtrasController::class, 'index']);
    Route::post('/horas-extras', [HorasExtrasController::class, 'store']);
    Route::put('/horas-extras/{id}', [HorasExtrasController::class, 'update']);
    Route::delete('/horas-extras/{id}', [HorasExtrasController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Perfil Breeze
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/horas-extras-gerencia', [HorasExtrasController::class, 'gerencia']);

    Route::get('/horas-extras/exportar', [HorasExtrasController::class, 'exportar']);
});

require __DIR__.'/auth.php';