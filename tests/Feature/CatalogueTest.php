<?php

namespace Tests\Feature;

use App\Models\Car;
use Tests\TestCase;

class CatalogueTest extends TestCase
{
    public function test_the_landing_page_opens(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Cómo funciona')
            // Los precios de los planes salen de la configuración, no escritos en la vista.
            ->assertSee('19,99 €');
    }

    public function test_the_catalogue_shows_a_published_car(): void
    {
        $car = Car::factory()->create([
            'brand' => 'Seat',
            'model' => 'Ibiza',
            'city' => 'Málaga',
            'province_code' => '29',
            'price_cents' => 3500,
        ]);

        $this->get(route('cars.index'))
            ->assertOk()
            ->assertSee('Seat Ibiza')
            ->assertSee('Málaga')
            ->assertSee('35 €')
            ->assertSee('1 coche publicado');

        $this->assertTrue($car->published);
    }

    public function test_the_card_does_not_repeat_the_place_when_the_city_is_the_capital(): void
    {
        Car::factory()->create(['city' => 'Málaga', 'province_code' => '29']);

        $this->get(route('cars.index'))
            ->assertOk()
            ->assertSee('Málaga')
            ->assertDontSee('Málaga · Málaga');
    }

    public function test_the_catalogue_hides_a_car_the_owner_took_down(): void
    {
        Car::factory()->create(['brand' => 'Renault', 'model' => 'Clio']);
        Car::factory()->hidden()->create(['brand' => 'Fiat', 'model' => 'Panda']);

        $this->get(route('cars.index'))
            ->assertOk()
            ->assertSee('Renault Clio')
            ->assertDontSee('Fiat Panda');
    }

    public function test_the_catalogue_says_so_when_there_is_nothing(): void
    {
        $this->get(route('cars.index'))
            ->assertOk()
            ->assertSee('Todavía no hay coches publicados.');
    }

    public function test_the_catalogue_does_not_query_once_per_car(): void
    {
        Car::factory()->count(5)->create();

        \DB::enableQueryLog();
        $this->get(route('cars.index'))->assertOk();
        $queries = count(\DB::getQueryLog());
        \DB::disableQueryLog();

        // Los coches, el total de la paginación, las provincias y las fotos: cuatro.
        // Si esto sube al crecer la rejilla, alguien se dejó un `with()`.
        $this->assertLessThanOrEqual(5, $queries, "La rejilla ha hecho {$queries} consultas.");
    }
}
