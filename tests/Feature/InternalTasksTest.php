<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InternalTasksTest extends TestCase
{
    public function test_without_a_token_the_route_does_not_even_exist(): void
    {
        config(['aparcado.internal_token' => 'el-token-bueno']);

        /*
         * 404 y no 401 a propósito: un 401 confirma que ahí hay algo. Para quien
         * llame sin la llave, esta ruta no existe.
         */
        $this->postJson(route('internal.close-bookings'))->assertNotFound();
    }

    public function test_a_wrong_token_does_not_open_it_either(): void
    {
        config(['aparcado.internal_token' => 'el-token-bueno']);

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

    public function test_with_the_right_token_it_closes_what_already_happened(): void
    {
        config(['aparcado.internal_token' => 'el-token-bueno']);

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
