<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SimController;
use App\Http\Controllers\ProfileController;

// --- ROTAS DAS TELAS ---
Route::get('/', [SimController::class, 'index'])->name('home');
Route::get('/empresa', [SimController::class, 'empresa'])->name('empresa');

// --- ROTAS DE API (MAPA E CÁLCULO) ---
Route::prefix('api')->group(function () {
    Route::post('/calcular-frete', [SimController::class, 'calcularFrete']);
    Route::get('/malha', [SimController::class, 'getMalha']);
    Route::get('/terminais', [SimController::class, 'getTerminais']);
    Route::get('/rota', [SimController::class, 'getRota']);
});

// --- ROTAS PROTEGIDAS ---
Route::middleware('auth')->group(function () {
    
    Route::get('/simulador', [SimController::class, 'simulador'])->name('simulador');

    // Dashboard original do Breeze
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware('verified')->name('dashboard');

    // Gestão de Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';