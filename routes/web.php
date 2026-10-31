<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SimController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TarifaFixaController;
use App\Models\Simulacao;

// --- ROTAS DAS TELAS ---
Route::get('/', [SimController::class, 'index'])->name('home');
Route::get('/sobre', [SimController::class, 'sobre'])->name('sobre');

// --- ROTAS DE API (MAPA E CÁLCULO) ---
Route::prefix('dados')->group(function () {
    Route::get('/malha', [SimController::class, 'getMalha']);
    Route::get('/terminais', [SimController::class, 'getTerminais']);
    Route::post('/calcular-frete', [SimController::class, 'calcularFrete']);
    Route::get('/rota', [SimController::class, 'getRota']);
});

// --- ROTAS PROTEGIDAS ---
Route::middleware('auth')->group(function () {

    Route::get('/simulador', [SimController::class, 'simulador'])->name('simulador');

    Route::post('/simulador/salvar', [SimController::class, 'salvarSimulacao'])->name('simulador.salvar');
    Route::delete('/simulador/{id}', [SimController::class, 'excluirSimulacao'])->name('simulador.excluir');

    Route::get('/dados/tarifas-fixas', [TarifaFixaController::class, 'index']);
    Route::put('/dados/tarifas-fixas', [TarifaFixaController::class, 'salvar']);
    Route::delete('/dados/tarifas-fixas', [TarifaFixaController::class, 'restaurar']);

    Route::get('/dashboard', function () {
        $simulacoes = Simulacao::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('dashboard', ['simulacoes' => $simulacoes]);
    })->middleware(['auth', 'verified'])->name('dashboard');

    // Gestão de Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';