<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Las dos puertas que se abren desde fuera —el aviso de PayPal y las tareas que
 * lanza GitHub Actions— no pueden traer un token de formulario, así que tienen que
 * estar exentas de la comprobación de CSRF.
 *
 * Esto **no se puede probar con una petición normal**: el middleware se salta
 * entero cuando corre la suite (`runningUnitTests()`), así que cualquier test por
 * HTTP pasaría aunque la exención no existiera. Por eso se le pregunta
 * directamente. El fallo que esto evita apareció desplegando, con un 419 donde
 * tenía que haber un 404.
 */
class CsrfExemptionsTest extends TestCase
{
    public function test_the_paypal_notice_does_not_need_a_form_token(): void
    {
        $this->assertTrue($this->exempt('/pagos/paypal/aviso'));
    }

    public function test_the_scheduled_tasks_do_not_need_one_either(): void
    {
        $this->assertTrue($this->exempt('/internal/close-bookings'));
        $this->assertTrue($this->exempt('/internal/verify'));
        $this->assertTrue($this->exempt('/internal/seed-demo'));
    }

    public function test_everything_else_still_needs_it(): void
    {
        // Lo que de verdad importa del test: que la exención sea una lista corta y
        // no una puerta abierta.
        $this->assertFalse($this->exempt('/entrar'));
        $this->assertFalse($this->exempt('/registro'));
        $this->assertFalse($this->exempt('/mis-coches'));
        $this->assertFalse($this->exempt('/reservas/1/cobrar'));
    }

    private function exempt(string $uri): bool
    {
        $middleware = new class(app(), app('encrypter')) extends PreventRequestForgery
        {
            // `inExceptArray()` es protegido, y es justo lo que hay que preguntar.
            public function exempts(Request $request): bool
            {
                return $this->inExceptArray($request);
            }
        };

        return $middleware->exempts(Request::create($uri, 'POST'));
    }
}
