<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Car;
use App\Models\Feature;
use App\Models\User;
use Tests\TestCase;

class CarPageTest extends TestCase
{
    public function test_the_page_shows_what_the_car_is(): void
    {
        $car = Car::factory()->create([
            'brand' => 'Seat',
            'model' => 'Ibiza',
            'price_cents' => 3500,
            'power_hp' => 110,
            'kilometres' => 84_000,
            'description' => 'Recién pasada la ITV.',
        ]);

        $this->get(route('cars.show', $car))
            ->assertOk()
            ->assertSee('Seat Ibiza')
            ->assertSee('35 €')
            ->assertSee('110 CV')
            ->assertSee('84.000 km')
            ->assertSee('Recién pasada la ITV.');
    }

    public function test_the_extras_come_out_grouped(): void
    {
        $car = Car::factory()->create();

        $car->features()->attach(
            Feature::query()->whereIn('slug', ['gps', 'isofix'])->pluck('id')
        );

        $this->get(route('cars.show', $car))
            ->assertOk()
            ->assertSee('Conectividad')
            ->assertSee('GPS')
            ->assertSee('Seguridad')
            ->assertSee('Fijación Isofix');
    }

    public function test_a_car_the_owner_took_down_is_not_there(): void
    {
        $hidden = Car::factory()->hidden()->create();

        // Aunque alguien tenga el enlace guardado de cuando sí estaba publicado.
        $this->get(route('cars.show', $hidden))->assertNotFound();
    }

    public function test_with_dates_it_says_it_is_free_and_what_it_would_cost(): void
    {
        $car = Car::factory()->create(['price_cents' => 4000]);

        $this->get(route('cars.show', ['car' => $car, 'from' => '2026-10-03', 'to' => '2026-10-07']))
            ->assertOk()
            ->assertSee('Libre del 3 al 7 de octubre')
            ->assertSee('5 días × 40 €')
            ->assertSee('200,00 €');
    }

    public function test_with_dates_that_are_taken_it_says_so_and_does_not_quote(): void
    {
        $car = Car::factory()->create(['price_cents' => 4000]);

        Booking::factory()->for($car)->between('2026-10-01', '2026-10-10')->create();

        $this->get(route('cars.show', ['car' => $car, 'from' => '2026-10-03', 'to' => '2026-10-07']))
            ->assertOk()
            ->assertSee('Este coche está cogido esos días.')
            ->assertDontSee('200,00 €');
    }

    public function test_the_street_address_is_not_public(): void
    {
        $car = Car::factory()->create([
            'address' => 'Calle Larios 14, 3º B',
            'city' => 'Málaga',
        ]);

        // Sitio sí, portal no: la dirección exacta es del dueño hasta que hay una
        // reserva. El TFG la pintaba en la ficha junto al mapa.
        $this->get(route('cars.show', $car))
            ->assertOk()
            ->assertSee('Málaga')
            ->assertDontSee('Calle Larios 14');
    }

    public function test_the_map_only_gets_the_rounded_coordinates(): void
    {
        $car = Car::factory()->create([
            'latitude' => 36.7212345,
            'longitude' => -4.4213456,
        ]);

        $response = $this->get(route('cars.show', $car))->assertOk();

        // Lo que sale hacia el mapa es la zona, no el punto: si las coordenadas
        // completas llegaran a la URL, cualquiera sabría dónde aparca.
        $response->assertDontSee('36.7212345');
        $response->assertDontSee('-4.4213456');
        $response->assertSee('openstreetmap.org/export/embed.html', escape: false);
    }

    public function test_it_says_whether_the_owner_is_verified(): void
    {
        $verified = Car::factory()->for(User::factory()->create(['name' => 'Lucía']), 'owner')->create();
        $pending = Car::factory()->for(User::factory()->pendingReview()->create(['name' => 'Diego']), 'owner')->create();

        $this->get(route('cars.show', $verified))->assertOk()->assertSee('Identidad verificada');
        $this->get(route('cars.show', $pending))->assertOk()->assertSee('Sin verificar');
    }

    public function test_the_card_carries_the_searched_dates_into_the_page(): void
    {
        $car = Car::factory()->create();

        $this->get(route('cars.index', ['from' => '2026-10-03', 'to' => '2026-10-07']))
            ->assertOk()
            ->assertSee(route('cars.show', ['car' => $car, 'from' => '2026-10-03', 'to' => '2026-10-07']));
    }
}
