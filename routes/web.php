<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SimController; 

// Rotas das Telas
Route::get('/', [SimController::class, 'index'])->name('home');
Route::get('/simulador', [SimController::class, 'simulador'])->name('simulador');
Route::get('/empresa', [SimController::class, 'empresa'])->name('empresa');

// Rotas de API
Route::prefix('api')->group(function () {
    Route::post('/calcular-frete', [SimController::class, 'calcularFrete']);
    Route::get('/malha', [SimController::class, 'getMalha']);
    Route::get('/terminais', [SimController::class, 'getTerminais']);
    Route::get('/rota', [SimController::class, 'getRota']);
});
