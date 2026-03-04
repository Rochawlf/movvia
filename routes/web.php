<?php

use Illuminate\Support\Facades\Route;
use App\Models\Ride;

// ESTA É A ROTA CORRETA (Ela busca o objeto no banco)
Route::get('ride/active/{ride}', function (Ride $ride) {
    return view('ride-active', ['ride' => $ride]);
})->middleware(['auth'])->name('ride.active');

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


Route::view('historico', 'ride-history-page')
    ->middleware(['auth', 'verified'])
    ->name('ride.history');

require __DIR__ . '/auth.php';
