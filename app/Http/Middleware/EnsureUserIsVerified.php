<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Publicar un coche o reservar uno exige que alguien haya mirado el DNI y el
 * carné. En lugar de un 403 en seco, manda al perfil diciendo qué falta: el
 * usuario no ha hecho nada mal, sólo le queda un paso.
 */
class EnsureUserIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isVerified()) {
            return $next($request);
        }

        // El aviso vale para las dos cosas que la verificación cierra —alquilar y
        // publicar—, porque por aquí pasan las dos.
        $message = $request->user()?->hasSentDocuments()
            ? 'Estamos mirando tus papeles. En cuanto estén, podrás alquilar y publicar.'
            : 'Para alquilar un coche o publicar el tuyo tenemos que ver tu documento de identidad y tu carné.';

        return redirect()->route('profile.show')->with('warning', $message);
    }
}
