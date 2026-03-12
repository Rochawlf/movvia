<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Models\Ride;
use App\Enums\UserRole;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rota pública
Route::view('/', 'welcome')->name('welcome');

// Rotas protegidas por autenticação
Route::middleware(['auth'])->group(function () {

    // Dashboard principal com redirecionamento por perfil
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->role === UserRole::Driver) {
            return redirect()->route('dashboard.driver');
        }

        return view('dashboard');
    })->middleware(['verified'])->name('dashboard');

    // Dashboard exclusiva do motorista
    Route::get('/dashboard-driver', function () {
        $user = auth()->user();

        abort_if($user->role !== UserRole::Driver, 403);

        return view('dashboard-driver');
    })->middleware(['verified'])->name('dashboard.driver');

    // Rotas de perfil COM CONTROLLER
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('profile');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // Histórico de corridas
    Route::view('/historico', 'ride-history-page')
        ->middleware(['verified'])
        ->name('ride.history');

    // Rota de corrida ativa (com binding de modelo)
    Route::get('/ride/active/{ride}', function (Ride $ride) {
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