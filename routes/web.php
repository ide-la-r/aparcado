<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MyCarController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/coches', [CarController::class, 'index'])->name('cars.index');
Route::get('/coches/{car}', [CarController::class, 'show'])->name('cars.show');

Route::middleware('guest')->group(function () {
    Route::get('/registro', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/registro', [RegisteredUserController::class, 'store']);

    Route::get('/entrar', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/entrar', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/salir', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/perfil', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/perfil/papeles', [ProfileController::class, 'documents'])->name('profile.documents');
    Route::put('/perfil/contrasena', [ProfileController::class, 'password'])->name('profile.password');

    // Mis coches: la lista se ve siempre, pero publicar o editar exige tener los
    // papeles comprobados.
    Route::get('/mis-coches', [MyCarController::class, 'index'])->name('my-cars.index');

    Route::middleware('identity')->group(function () {
        Route::get('/mis-coches/nuevo', [MyCarController::class, 'create'])->name('my-cars.create');
        Route::post('/mis-coches', [MyCarController::class, 'store'])->name('my-cars.store');
        Route::get('/mis-coches/{car}/editar', [MyCarController::class, 'edit'])->name('my-cars.edit');
        Route::patch('/mis-coches/{car}', [MyCarController::class, 'update'])->name('my-cars.update');
        Route::delete('/mis-coches/{car}', [MyCarController::class, 'destroy'])->name('my-cars.destroy');
        Route::delete('/mis-coches/{car}/fotos/{photo}', [MyCarController::class, 'destroyPhoto'])
            ->name('my-cars.photos.destroy');
    });
});
