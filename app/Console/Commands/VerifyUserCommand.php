<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Verificar una cuenta a mano. No hay panel de administración —y para un proyecto
 * de una persona tampoco hace falta—, así que quien mira los papeles los mira en
 * el disco privado y da el visto bueno desde aquí.
 */
class VerifyUserCommand extends Command
{
    protected $signature = 'aparcado:verify {email} {--undo : Quitar la verificación}';

    protected $description = 'Marca una cuenta como verificada tras comprobar sus papeles';

    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->error('No hay ninguna cuenta con ese correo.');

            return self::FAILURE;
        }

        if ($this->option('undo')) {
            $this->stamp($user, null);
            $this->info("Verificación retirada a {$user->fullName()}.");

            return self::SUCCESS;
        }

        if (! $user->hasSentDocuments()) {
            $this->error("{$user->fullName()} todavía no ha enviado los dos papeles.");

            return self::FAILURE;
        }

        $this->stamp($user, now());
        $this->info("{$user->fullName()} queda verificado.");

        return self::SUCCESS;
    }

    /**
     * Con asignación directa y no con `update()`: `verified_at` se queda fuera de
     * `$fillable` a propósito —que no se pueda verificar una cuenta desde un
     * formulario—, y un `update()` con un campo no asignable no falla, se lo salta
     * sin decir nada.
     */
    private function stamp(User $user, ?Carbon $moment): void
    {
        $user->verified_at = $moment;
        $user->save();
    }
}
