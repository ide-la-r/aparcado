<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InternalTaskController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MyCarController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/coches', [CarController::class, 'index'])->name('cars.index');
Route::get('/coches/{car}', [CarController::class, 'show'])->name('cars.show');

// Páginas de siempre: sin datos, sin sesión y sin controlador.
Route::view('/sobre-aparcado', 'pages.about')->name('pages.about');
Route::view('/ayuda', 'pages.help')->name('pages.help');
Route::view('/contacto', 'pages.contact')->name('pages.contact');
Route::view('/aviso-legal', 'pages.legal')->name('pages.legal');
Route::view('/privacidad', 'pages.privacy')->name('pages.privacy');
Route::view('/cookies', 'pages.cookies')->name('pages.cookies');
Route::view('/creditos', 'pages.credits')->name('pages.credits');

// El aviso de PayPal viene de fuera: sin sesión, sin usuario y sin token, así que
// no puede pasar por la comprobación de CSRF. Lo que lo protege es su firma.
Route::post('/pagos/paypal/aviso', [PaymentController::class, 'webhook'])->name('payments.webhook');

// Lo que en un servidor normal haría el cron. En el plan gratuito de Render no
// hay ni cron ni trabajadores, así que lo llama un flujo de GitHub Actions una
// vez al día, y de paso despierta el contenedor dormido.
Route::middleware(['internal'])->prefix('internal')->group(function () {
    Route::post('/close-bookings', [InternalTaskController::class, 'closeBookings'])
        ->name('internal.close-bookings');

    /*
     * Y las de administración. Están aquí porque en el plan gratuito de Render **no
     * hay consola** —el Shell es de pago—, así que sin ellas no habría forma de
     * verificar una cuenta en producción y nadie podría publicar un coche.
     */
    Route::post('/verify', [InternalTaskController::class, 'verify'])->name('internal.verify');
    Route::post('/seed-demo', [InternalTaskController::class, 'seedDemo'])->name('internal.seed-demo');

    // Reparar las fotos de los ejemplos: se sembraron antes de que las fotos
    // existieran, y sembrar otra vez no vale porque ya hay coches.
    Route::post('/refresh-demo-photos', [InternalTaskController::class, 'refreshDemoPhotos'])
        ->name('internal.refresh-demo-photos');
});

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

    // Las listas se ven siempre; pedir un coche o publicar el tuyo exige tener los
    // papeles comprobados. Hablar con el dueño, no: para eso está el chat.
    Route::get('/mis-coches', [MyCarController::class, 'index'])->name('my-cars.index');
    Route::get('/mis-reservas', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/reservas-de-mis-coches', [BookingController::class, 'incoming'])->name('bookings.incoming');
    Route::patch('/reservas/{booking}/cancelar', [BookingController::class, 'cancel'])->name('bookings.cancel');

    Route::get('/reservas/{booking}/pagar', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/reservas/{booking}/orden', [PaymentController::class, 'createOrder'])->name('payments.order');
    Route::post('/reservas/{booking}/cobrar', [PaymentController::class, 'capture'])->name('payments.capture');

    Route::get('/mensajes', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/coches/{car}/escribir', [MessageController::class, 'start'])->name('messages.start');
    Route::get('/mensajes/{conversation}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/mensajes/{conversation}', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/mensajes/{conversation}/nuevos', [MessageController::class, 'poll'])->name('messages.poll');

    Route::middleware('identity')->group(function () {
        Route::get('/mis-coches/nuevo', [MyCarController::class, 'create'])->name('my-cars.create');
        Route::post('/mis-coches', [MyCarController::class, 'store'])->name('my-cars.store');
        Route::get('/mis-coches/{car}/editar', [MyCarController::class, 'edit'])->name('my-cars.edit');
        Route::patch('/mis-coches/{car}', [MyCarController::class, 'update'])->name('my-cars.update');
        Route::delete('/mis-coches/{car}', [MyCarController::class, 'destroy'])->name('my-cars.destroy');
        Route::delete('/mis-coches/{car}/fotos/{photo}', [MyCarController::class, 'destroyPhoto'])
            ->name('my-cars.photos.destroy');

        Route::post('/coches/{car}/reservar', [BookingController::class, 'store'])->name('bookings.store');
    });
});
