<?php

use App\Http\Middleware\EnsureInternalToken;
use App\Http\Middleware\EnsureUserIsVerified;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         * Render termina el TLS en su balanceador y reenvía la petición por HTTP.
         * Sin confiar en él, la aplicación se cree que la petición es insegura y
         * pasan dos cosas, las dos medidas en el primer despliegue:
         *
         *  · Genera los enlaces del CSS y del JavaScript en `http://`, y el
         *    navegador los bloquea por contenido mixto: la web sale en HTML pelado.
         *  · `request()->ip()` devuelve la IP interna del proxy para todo el mundo,
         *    así que el límite de intentos de entrada por IP agrupa a todos los
         *    usuarios en el mismo cubo.
         *
         * `'*'` es lo que la documentación recomienda para este tipo de
         * plataformas: sus balanceadores no tienen IP fija, y al contenedor no se
         * llega si no es a través de ellos.
         */
        $middleware->trustProxies(at: '*');

        // Quien ya ha entrado y vuelve a pedir el formulario de entrada va a la
        // portada. Sin esto Laravel lo manda a `/dashboard`, que aquí no existe.
        $middleware->redirectUsersTo('/');

        $middleware->alias([
            'identity' => EnsureUserIsVerified::class,
            'internal' => EnsureInternalToken::class,
        ]);

        /*
         * Las dos puertas que se abren desde fuera y no pueden traer un token de
         * formulario: el aviso de PayPal y las tareas que lanza GitHub Actions.
         *
         * Va aquí y no con un `withoutMiddleware()` en la ruta porque eso hay que
         * acertarlo con el nombre exacto de la clase, y en Laravel 13 la buena es
         * `PreventRequestForgery` —`VerifyCsrfToken` y `ValidateCsrfToken` son
         * alias obsoletos—. Excluir el alias no excluye nada, y el fallo no se ve
         * en los tests porque ese middleware se salta entero cuando corre la suite:
         * apareció desplegando, con un 419 donde tenía que haber un 404.
         */
        $middleware->preventRequestForgery(except: [
            'pagos/paypal/aviso',
            'internal/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
