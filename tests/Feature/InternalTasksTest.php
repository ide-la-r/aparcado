<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Car;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InternalTasksTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['aparcado.internal_token' => 'el-token-bueno']);
    }

    public function test_without_a_token_the_route_does_not_even_exist(): void
    {
        /*
         * 404 y no 401 a propósito: un 401 confirma que ahí hay algo. Para quien
         * llame sin la llave, esta ruta no existe.
         */
        $this->postJson(route('internal.close-bookings'))->assertNotFound();
    }

    public function test_a_wrong_token_does_not_open_it_either(): void
    {
        $this->postJson(route('internal.close-bookings'), [], [
            'Authorization' => 'Bearer el-token-malo',
        ])->assertNotFound();
    }

    public function test_an_empty_token_in_the_environment_keeps_the_door_shut(): void
    {
        config(['aparcado.internal_token' => null]);

        // Una llave vacía no es una llave: si alguien despliega sin configurar el
        // token, la puerta se queda cerrada y no abierta a cualquiera.
        $this->postJson(route('internal.close-bookings'), [], [
            'Authorization' => 'Bearer ',
        ])->assertNotFound();

        $this->postJson(route('internal.close-bookings'))->assertNotFound();
    }

    public function test_the_admin_routes_are_behind_the_same_door(): void
    {
        foreach (['internal.verify', 'internal.seed-demo'] as $route) {
            $this->postJson(route($route), ['email' => 'yo@aparcado.test'])->assertNotFound();
        }
    }

    public function test_it_verifies_an_account_so_it_can_publish(): void
    {
        /*
         * Sin esto no se podría verificar a nadie en producción: el plan gratuito
         * de Render no tiene consola —el Shell es de pago—, así que el comando de
         * artisan no se puede lanzar y nadie podría publicar un coche.
         */
        $user = User::factory()->pendingReview()->create(['email' => 'yo@aparcado.test']);

        $this->postJson(route('internal.verify'), ['email' => 'yo@aparcado.test'], [
            'Authorization' => 'Bearer el-token-bueno',
        ])->assertOk()->assertJson(['ok' => true]);

        $this->assertTrue($user->refresh()->isVerified());
    }

    public function test_verifying_an_unknown_email_says_so(): void
    {
        $this->postJson(route('internal.verify'), ['email' => 'nadie@aparcado.test'], [
            'Authorization' => 'Bearer el-token-bueno',
        ])->assertNotFound()->assertJson(['ok' => false]);
    }

    public function test_the_demo_data_can_be_seeded_from_outside(): void
    {
        $this->postJson(route('internal.seed-demo'), [], [
            'Authorization' => 'Bearer el-token-bueno',
        ])->assertOk()->assertJson(['ok' => true, 'coches' => 12]);
    }

    public function test_the_demo_data_refuses_to_touch_a_database_with_cars(): void
    {
        Car::factory()->create();

        // Es la condición que hace que esta ruta no pueda hacer daño: en cuanto hay
        // algo de verdad dentro, deja de funcionar.
        $this->postJson(route('internal.seed-demo'), [], [
            'Authorization' => 'Bearer el-token-bueno',
        ])->assertStatus(409);

        $this->assertSame(1, Car::query()->count());
    }

    public function test_with_the_right_token_it_closes_what_already_happened(): void
    {
        $past = Booking::factory()->between(
            Carbon::today()->subDays(10)->toDateString(),
            Carbon::today()->subDays(5)->toDateString(),
        )->create();

        $this->postJson(route('internal.close-bookings'), [], [
            'Authorization' => 'Bearer el-token-bueno',
        ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame(BookingStatus::Completed, $past->refresh()->status);
    }
}
