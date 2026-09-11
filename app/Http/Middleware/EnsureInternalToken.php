<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * La puerta de las tareas programadas.
 *
 * En el plan gratuito de Render no hay ni cron ni trabajadores, así que quien
 * dispara las tareas de cada día es un flujo de GitHub Actions llamando a una
 * ruta. Esa ruta no tiene sesión ni usuario, así que lo único que la separa de
 * internet es este token.
 */
class EnsureInternalToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('aparcado.internal_token');

        // Sin token configurado no se abre: un valor vacío no puede valer como
        // llave, y en local eso significa que la puerta está cerrada.
        abort_if(blank($expected), 404);

        // Comparación en tiempo constante: comparar con `!==` filtra por cuánto
        // tarda en fallar cuántos caracteres del principio ha acertado quien
        // prueba.
        abort_unless(hash_equals($expected, (string) $request->bearerToken()), 404);

        return $next($request);
    }
}
