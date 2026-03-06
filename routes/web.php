<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Models\Ride;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rota pública
Route::view('/', 'welcome')->name('welcome');

// Rotas protegidas por autenticação
Route::middleware(['auth'])->group(function () {

    // Dashboard (requer verificação de email)
    Route::view('dashboard', 'dashboard')
        ->middleware(['verified'])
        ->name('dashboard');

    // Rotas de perfil COM CONTROLLER
    Route::prefix('profile')->group(function () {
        // Definimos o nome 'profile' diretamente aqui para a edição
        Route::get('/', [ProfileController::class, 'edit'])->name('profile');

        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // Rota simples de visualização de perfil (opcional - se quiser manter)
    // Route::view('perfil', 'profile')->name('perfil');

    // Histórico de corridas
    Route::view('historico', 'ride-history-page')
        ->middleware(['verified'])
        ->name('ride.history');

    // Rota de corrida ativa (com binding de modelo)
    Route::get('ride/active/{ride}', function (Ride $ride) {
        return view('ride-active', ['ride' => $ride]);
    })->name('ride.active');
});

// Rotas de perfil padronizadas
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Inclui rotas de autenticação (login, registro, etc)
require __DIR__ . '/auth.php';
