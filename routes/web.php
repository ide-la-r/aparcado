<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\HomeController;
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
});
