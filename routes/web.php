<?php

use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ResumenPaletasController;
use App\Http\Controllers\DistribucionLotesController;
use App\Http\Controllers\HorasExtrasController;
use Illuminate\Support\Facades\Route;


Route::get('/', fn () => redirect('/resumen-paletas'));

Route::get('/resumen-paletas', [ResumenPaletasController::class, 'index']);
Route::post('/resumen-paletas/procesar', [ResumenPaletasController::class, 'procesar']);

Route::post(
    '/resumen-paletas/exportar',
    [ResumenPaletasController::class,'exportar']
);

// Route::get('/reporte', [ReporteController::class, 'index']);
// Route::post('/reporte/procesar', [ReporteController::class, 'procesar']);


Route::get('/distribucion-lotes', [DistribucionLotesController::class, 'index']);
Route::post('/distribucion-lotes/procesar', [DistribucionLotesController::class, 'procesar']);
Route::post('/distribucion-lotes/exportar', [DistribucionLotesController::class, 'exportar']);


Route::get('/horas-extras', [HorasExtrasController::class, 'index']);
Route::post('/horas-extras', [HorasExtrasController::class, 'store']);
Route::put('/horas-extras/{id}', [HorasExtrasController::class, 'update']);
Route::delete('/horas-extras/{id}', [HorasExtrasController::class, 'destroy']);
