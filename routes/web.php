<?php

use App\Http\Controllers\CarController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/coches', [CarController::class, 'index'])->name('cars.index');
