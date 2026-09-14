<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Las tareas de administración, detrás del token compartido.
 *
 * Existen porque en el plan gratuito de Render **no hay consola**: el Shell es de
 * pago. Sin esto no habría forma de verificar una cuenta ni de sembrar nada en
 * producción, así que la aplicación quedaría mirándose a sí misma: nadie podría
 * publicar un coche.
 *
 * La puerta es `EnsureInternalToken`, que devuelve 404 si el token no cuadra.
 */
class InternalTaskController extends Controller
{
    /** Lo que en un servidor normal haría el cron. */
    public function closeBookings(): JsonResponse
    {
        Artisan::call('aparcado:close-bookings');

        return response()->json(['ok' => true, 'output' => trim(Artisan::output())]);
    }

    /**
     * Da por buenos los papeles de una cuenta.
     *
     * Aquí no se comprueba que los haya subido, al revés que el comando de consola:
     * quien llama es quien administra, y puede estar dando de alta su propia cuenta
     * en un despliegue recién hecho.
     */
    public function verify(Request $request): JsonResponse
    {
        $email = (string) $request->input('email');
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            return response()->json(['ok' => false, 'error' => 'No hay ninguna cuenta con ese correo.'], 404);
        }

        // Por asignación directa: `verified_at` está fuera de `$fillable` a
        // propósito, y un `update()` con un campo no asignable se lo salta sin
        // decir nada.
        $user->verified_at = now();
        $user->save();

        return response()->json(['ok' => true, 'verified' => $user->email]);
    }

    /**
     * Siembra los datos de ejemplo, y **sólo sobre una base de datos sin coches**.
     *
     * Esa condición es lo que hace que esta ruta no pueda hacer daño: no duplica
     * nada, no pisa nada y deja de funcionar en cuanto hay algo de verdad dentro.
     */
    public function seedDemo(): JsonResponse
    {
        if (Car::query()->exists()) {
            return response()->json([
                'ok' => false,
                'error' => 'Ya hay coches publicados: los datos de ejemplo sólo se siembran sobre una base de datos vacía.',
            ], 409);
        }

        Artisan::call('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);

        return response()->json([
            'ok' => true,
            'usuarios' => User::query()->count(),
            'coches' => Car::query()->count(),
        ]);
    }
}
