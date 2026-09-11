<?php

namespace Tests\Feature;

use App\Enums\Plan;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Catalogue\CarSearch;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CarSearchTest extends TestCase
{
    public function test_it_only_brings_cars_of_the_province_asked_for(): void
    {
        Car::factory()->create(['province_code' => '29', 'brand' => 'Seat', 'model' => 'Ibiza']);
        Car::factory()->create(['province_code' => '28', 'brand' => 'Fiat', 'model' => 'Panda']);

        $found = CarSearch::fromFilters(['province' => '29'])->query()->get();

        $this->assertCount(1, $found);
        $this->assertSame('Seat Ibiza', $found->first()->title());
    }

    public function test_it_hides_the_cars_already_taken_on_those_dates(): void
    {
        $free = Car::factory()->create(['brand' => 'Libre']);
        $taken = Car::factory()->create(['brand' => 'Cogido']);

        Booking::factory()->for($taken)->between('2026-10-10', '2026-10-15')->create();

        $found = CarSearch::fromFilters(['from' => '2026-10-12', 'to' => '2026-10-14'])->query()->get();

        $this->assertTrue($found->contains('id', $free->id));
        $this->assertFalse($found->contains('id', $taken->id));
    }

    public function test_a_premium_owner_goes_before_a_plus_one_and_both_before_nobody(): void
    {
        // A propósito al revés de lo que se espera, y con el más caro arriba: si el
        // orden no mirara el plan, saldrían por precio y este test fallaría.
        $nobody = $this->ownerWithPlan(null);
        $plus = $this->ownerWithPlan(Plan::Plus);
        $premium = $this->ownerWithPlan(Plan::Premium);

        Car::factory()->for($nobody, 'owner')->create(['price_cents' => 1000]);
        Car::factory()->for($plus, 'owner')->create(['price_cents' => 5000]);
        Car::factory()->for($premium, 'owner')->create(['price_cents' => 9000]);

        $owners = CarSearch::fromFilters([])->query()->get()->pluck('owner_id');

        $this->assertSame([$premium->id, $plus->id, $nobody->id], $owners->all());
    }

    public function test_an_expired_plan_stops_pushing_the_car_up(): void
    {
        $expired = $this->ownerWithPlan(null);
        Subscription::factory()->for($expired)->plan(Plan::Premium)->expired()->create();

        $paying = $this->ownerWithPlan(Plan::Plus);

        Car::factory()->for($expired, 'owner')->create(['price_cents' => 9000]);
        Car::factory()->for($paying, 'owner')->create(['price_cents' => 9000]);

        $owners = CarSearch::fromFilters([])->query()->get()->pluck('owner_id');

        $this->assertSame([$paying->id, $expired->id], $owners->all());
    }

    public function test_two_overlapping_plans_do_not_duplicate_the_car(): void
    {
        $owner = $this->ownerWithPlan(Plan::Plus);
        Subscription::factory()->for($owner)->plan(Plan::Premium)->create();

        Car::factory()->for($owner, 'owner')->create();

        // Con un join en lugar de la subconsulta, este coche saldría dos veces en
        // la rejilla y la paginación mentiría.
        $this->assertCount(1, CarSearch::fromFilters([])->query()->get());
    }

    public function test_within_the_same_plan_the_cheapest_goes_first(): void
    {
        $owner = $this->ownerWithPlan(null);

        $expensive = Car::factory()->for($owner, 'owner')->create(['price_cents' => 7000]);
        $cheap = Car::factory()->for($owner, 'owner')->create(['price_cents' => 2000]);

        $this->assertSame(
            [$cheap->id, $expensive->id],
            CarSearch::fromFilters([])->query()->pluck('cars.id')->all(),
        );
    }

    public function test_the_form_refuses_dates_in_the_past(): void
    {
        $yesterday = Carbon::yesterday()->toDateString();

        $this->get(route('cars.index', ['from' => $yesterday, 'to' => Carbon::today()->toDateString()]))
            ->assertSessionHasErrors(['from' => 'No se puede alquilar un coche en el pasado.']);
    }

    public function test_the_form_refuses_a_return_date_before_the_pick_up(): void
    {
        $this->get(route('cars.index', [
            'from' => Carbon::today()->addDays(5)->toDateString(),
            'to' => Carbon::today()->addDays(2)->toDateString(),
        ]))->assertSessionHasErrors(['to' => 'El día de salida no puede ser anterior al de entrada.']);
    }

    public function test_the_form_refuses_a_rental_longer_than_the_configured_limit(): void
    {
        config(['aparcado.bookings.max_days' => 7]);

        $this->get(route('cars.index', [
            'from' => Carbon::today()->addDay()->toDateString(),
            'to' => Carbon::today()->addDays(9)->toDateString(),
        ]))->assertSessionHasErrors(['to' => 'Un alquiler no puede durar más de 7 días.']);
    }

    public function test_one_date_without_the_other_is_not_a_search(): void
    {
        $this->get(route('cars.index', ['from' => Carbon::today()->addDay()->toDateString()]))
            ->assertSessionHasErrors('to');
    }

    public function test_an_empty_field_is_not_a_filter(): void
    {
        Car::factory()->create(['brand' => 'Seat', 'model' => 'Ibiza']);

        // El formulario manda los huecos vacíos como cadena vacía; si eso llegara a
        // la búsqueda, filtraría por la provincia '' y no saldría nada.
        $this->get(route('cars.index', ['province' => '', 'from' => '', 'to' => '']))
            ->assertOk()
            ->assertSee('Seat Ibiza');
    }

    public function test_it_says_which_dates_and_province_were_searched(): void
    {
        Car::factory()->create(['province_code' => '29']);

        $this->get(route('cars.index', [
            'province' => '29',
            'from' => '2026-10-03',
            'to' => '2026-10-07',
        ]))
            ->assertOk()
            ->assertSee('libres del 3 al 7 de octubre')
            ->assertSee('en Málaga');
    }

    private function ownerWithPlan(?Plan $plan): User
    {
        $owner = User::factory()->create();

        if ($plan !== null) {
            Subscription::factory()->for($owner)->plan($plan)->create();
        }

        return $owner;
    }
}
