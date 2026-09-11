<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Car;
use Tests\TestCase;

class CarAvailabilityTest extends TestCase
{
    public function test_a_car_with_a_confirmed_booking_is_not_free_on_those_dates(): void
    {
        $car = Car::factory()->create();

        Booking::factory()->for($car)->between('2026-10-10', '2026-10-15')->create();

        $this->assertFalse(
            Car::query()->freeBetween('2026-10-12', '2026-10-14')->whereKey($car->id)->exists()
        );
    }

    public function test_a_booking_that_ends_before_the_search_starts_leaves_the_car_free(): void
    {
        $car = Car::factory()->create();

        Booking::factory()->for($car)->between('2026-10-01', '2026-10-05')->create();

        $this->assertTrue(
            Car::query()->freeBetween('2026-10-06', '2026-10-09')->whereKey($car->id)->exists()
        );
    }

    public function test_the_last_day_of_a_booking_still_counts_as_taken(): void
    {
        $car = Car::factory()->create();

        Booking::factory()->for($car)->between('2026-10-01', '2026-10-05')->create();

        // Quien devuelve el coche el día 5 no puede entregarlo y recogerlo el mismo
        // día: el día de fin está incluido.
        $this->assertFalse(
            Car::query()->freeBetween('2026-10-05', '2026-10-08')->whereKey($car->id)->exists()
        );
    }

    public function test_a_booking_that_swallows_the_whole_search_also_takes_the_car(): void
    {
        $car = Car::factory()->create();

        Booking::factory()->for($car)->between('2026-10-01', '2026-10-31')->create();

        $this->assertFalse(
            Car::query()->freeBetween('2026-10-10', '2026-10-12')->whereKey($car->id)->exists()
        );
    }

    public function test_a_booking_that_is_still_unpaid_does_not_take_the_car(): void
    {
        $car = Car::factory()->create();

        Booking::factory()->for($car)->between('2026-10-10', '2026-10-15')->pending()->create();

        // En el TFG cualquier reserva bloqueaba las fechas, así que pedir un coche y
        // no pagar lo sacaba del catálogo.
        $this->assertTrue(
            Car::query()->freeBetween('2026-10-12', '2026-10-14')->whereKey($car->id)->exists()
        );
    }

    public function test_a_cancelled_booking_frees_the_dates_again(): void
    {
        $car = Car::factory()->create();

        Booking::factory()->for($car)->between('2026-10-10', '2026-10-15')->cancelled()->create();

        $this->assertTrue(
            Car::query()->freeBetween('2026-10-12', '2026-10-14')->whereKey($car->id)->exists()
        );
    }

    public function test_a_hidden_car_stays_out_of_the_catalogue(): void
    {
        $visible = Car::factory()->create();
        $hidden = Car::factory()->hidden()->create();

        $ids = Car::query()->published()->pluck('id');

        $this->assertTrue($ids->contains($visible->id));
        $this->assertFalse($ids->contains($hidden->id));
    }
}
