<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Car;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookingsTest extends TestCase
{
    public function test_a_guest_cannot_book(): void
    {
        $car = Car::factory()->create();

        $this->post(route('bookings.store', $car), $this->dates())->assertRedirect(route('login'));
    }

    public function test_someone_without_papers_is_sent_to_their_profile(): void
    {
        $car = Car::factory()->create();

        $this->actingAs(User::factory()->pendingReview()->create())
            ->post(route('bookings.store', $car), $this->dates())
            ->assertRedirect(route('profile.show'));

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_someone_verified_can_ask_for_a_car(): void
    {
        $car = Car::factory()->create(['price_cents' => 4000]);
        $renter = User::factory()->create();

        $this->actingAs($renter)
            ->post(route('bookings.store', $car), $this->dates('+10 days', '+14 days'))
            ->assertRedirect(route('bookings.index'));

        $booking = Booking::query()->firstOrFail();

        $this->assertSame($renter->id, $booking->renter_id);
        $this->assertSame(5, $booking->days);
        $this->assertSame(4000, $booking->price_cents_per_day);
        $this->assertSame(20000, $booking->total_cents);
        $this->assertSame(BookingStatus::Pending, $booking->status);
    }

    public function test_asking_for_a_car_does_not_take_it_off_the_catalogue(): void
    {
        $car = Car::factory()->create();
        [$from, $to] = array_values($this->dates('+10 days', '+14 days'));

        $this->actingAs(User::factory()->create())->post(route('bookings.store', $car), compact('from', 'to'));

        /*
         * Lo importante: una reserva sin pagar no ocupa nada. En el TFG cualquier
         * reserva bloqueaba las fechas, así que pedir un coche y no pagarlo era la
         * forma de tumbar al de al lado sin gastar un euro.
         */
        $this->assertTrue(
            Car::query()->freeBetween($from, $to)->whereKey($car->id)->exists()
        );
    }

    public function test_the_price_is_frozen_the_day_it_was_asked_for(): void
    {
        $car = Car::factory()->create(['price_cents' => 4000]);

        $this->actingAs(User::factory()->create())
            ->post(route('bookings.store', $car), $this->dates('+10 days', '+11 days'));

        $car->update(['price_cents' => 9000]);

        $this->assertSame(4000, Booking::query()->value('price_cents_per_day'));
        $this->assertSame(8000, Booking::query()->value('total_cents'));
    }

    public function test_nobody_books_their_own_car(): void
    {
        $owner = User::factory()->create();
        $car = Car::factory()->for($owner, 'owner')->create();

        $this->actingAs($owner)
            ->post(route('bookings.store', $car), $this->dates())
            ->assertForbidden();
    }

    public function test_a_hidden_car_cannot_be_booked_through_the_direct_link(): void
    {
        $car = Car::factory()->hidden()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('bookings.store', $car), $this->dates())
            ->assertNotFound();
    }

    public function test_dates_that_somebody_already_paid_for_are_refused(): void
    {
        $car = Car::factory()->create();
        $from = Carbon::today()->addDays(10)->toDateString();
        $to = Carbon::today()->addDays(14)->toDateString();

        Booking::factory()->for($car)->between($from, $to)->create();

        $this->actingAs(User::factory()->create())
            ->post(route('bookings.store', $car), ['from' => $from, 'to' => $to])
            ->assertSessionHasErrors(['to' => 'Alguien se ha adelantado: el coche ya está cogido esos días.']);
    }

    public function test_two_people_can_have_it_pending_and_the_first_to_pay_wins(): void
    {
        $car = Car::factory()->create();
        $dates = $this->dates('+10 days', '+14 days');

        $this->actingAs(User::factory()->create())->post(route('bookings.store', $car), $dates);
        $this->actingAs(User::factory()->create())->post(route('bookings.store', $car), $dates);

        // Nadie ha pagado todavía, así que las dos peticiones son legítimas.
        $this->assertSame(2, Booking::query()->count());
    }

    public function test_asking_twice_for_the_same_dates_does_not_leave_two_bookings(): void
    {
        $car = Car::factory()->create();
        $renter = User::factory()->create();
        $dates = $this->dates('+10 days', '+14 days');

        $this->actingAs($renter)->post(route('bookings.store', $car), $dates);
        $this->actingAs($renter)->post(route('bookings.store', $car), $dates);

        // Recargar la página o darle dos veces al botón no debería dejar dos
        // reservas que pagar.
        $this->assertSame(1, Booking::query()->count());
    }

    public function test_the_past_is_not_bookable(): void
    {
        $car = Car::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('bookings.store', $car), [
                'from' => Carbon::yesterday()->toDateString(),
                'to' => Carbon::today()->toDateString(),
            ])
            ->assertSessionHasErrors(['from' => 'No se puede alquilar un coche en el pasado.']);
    }

    public function test_a_rental_longer_than_the_limit_is_refused(): void
    {
        config(['aparcado.bookings.max_days' => 7]);

        $car = Car::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('bookings.store', $car), $this->dates('+2 days', '+20 days'))
            ->assertSessionHasErrors(['to' => 'Un alquiler no puede durar más de 7 días.']);
    }

    public function test_the_list_only_shows_my_own_bookings(): void
    {
        $renter = User::factory()->create();

        Booking::factory()->for($renter, 'renter')
            ->for(Car::factory()->create(['brand' => 'Seat', 'model' => 'Ibiza']))
            ->create();

        Booking::factory()->for(Car::factory()->create(['brand' => 'Fiat', 'model' => 'Panda']))->create();

        $this->actingAs($renter)
            ->get(route('bookings.index'))
            ->assertOk()
            ->assertSee('Seat Ibiza')
            ->assertDontSee('Fiat Panda');
    }

    public function test_the_owner_sees_what_they_have_been_asked_for(): void
    {
        $owner = User::factory()->create();
        $mine = Car::factory()->for($owner, 'owner')->create(['brand' => 'Seat', 'model' => 'León']);

        Booking::factory()->for($mine)->create();
        Booking::factory()->for(Car::factory()->create(['brand' => 'Fiat', 'model' => 'Panda']))->create();

        $this->actingAs($owner)
            ->get(route('bookings.incoming'))
            ->assertOk()
            ->assertSee('Seat León')
            ->assertDontSee('Fiat Panda');
    }

    public function test_both_sides_can_cancel_before_it_starts(): void
    {
        $owner = User::factory()->create();
        $car = Car::factory()->for($owner, 'owner')->create();

        $mine = Booking::factory()->for($car)->between(
            Carbon::today()->addDays(5)->toDateString(),
            Carbon::today()->addDays(7)->toDateString(),
        )->create();

        $this->actingAs($mine->renter)->patch(route('bookings.cancel', $mine))->assertRedirect();
        $this->assertSame(BookingStatus::Cancelled, $mine->refresh()->status);

        $theirs = Booking::factory()->for($car)->between(
            Carbon::today()->addDays(20)->toDateString(),
            Carbon::today()->addDays(22)->toDateString(),
        )->create();

        $this->actingAs($owner)->patch(route('bookings.cancel', $theirs))->assertRedirect();
        $this->assertSame(BookingStatus::Cancelled, $theirs->refresh()->status);
    }

    public function test_a_stranger_cannot_cancel_a_booking(): void
    {
        $booking = Booking::factory()->between(
            Carbon::today()->addDays(5)->toDateString(),
            Carbon::today()->addDays(7)->toDateString(),
        )->create();

        $this->actingAs(User::factory()->create())
            ->patch(route('bookings.cancel', $booking))
            ->assertForbidden();

        $this->assertSame(BookingStatus::Confirmed, $booking->refresh()->status);
    }

    public function test_a_rental_that_already_started_cannot_be_cancelled_with_a_button(): void
    {
        $booking = Booking::factory()->between(
            Carbon::today()->toDateString(),
            Carbon::today()->addDays(3)->toDateString(),
        )->create();

        // Con el coche ya entregado, lo que pase se arregla entre las dos personas.
        $this->actingAs($booking->renter)
            ->patch(route('bookings.cancel', $booking))
            ->assertForbidden();
    }

    public function test_cancelling_frees_the_dates_again(): void
    {
        $car = Car::factory()->create();
        $from = Carbon::today()->addDays(10)->toDateString();
        $to = Carbon::today()->addDays(14)->toDateString();

        $booking = Booking::factory()->for($car)->between($from, $to)->create();

        $this->assertFalse(Car::query()->freeBetween($from, $to)->whereKey($car->id)->exists());

        $this->actingAs($booking->renter)->patch(route('bookings.cancel', $booking));

        $this->assertTrue(Car::query()->freeBetween($from, $to)->whereKey($car->id)->exists());
    }

    public function test_the_command_closes_what_already_happened(): void
    {
        $past = Booking::factory()->between(
            Carbon::today()->subDays(10)->toDateString(),
            Carbon::today()->subDays(5)->toDateString(),
        )->create();

        $ongoing = Booking::factory()->between(
            Carbon::today()->toDateString(),
            Carbon::today()->addDays(3)->toDateString(),
        )->create();

        $this->artisan('aparcado:close-bookings')->assertSuccessful();

        $this->assertSame(BookingStatus::Completed, $past->refresh()->status);
        $this->assertSame(BookingStatus::Confirmed, $ongoing->refresh()->status);
    }

    public function test_a_closed_booking_still_owns_its_dates(): void
    {
        $car = Car::factory()->create();
        $from = Carbon::today()->subDays(10)->toDateString();
        $to = Carbon::today()->subDays(5)->toDateString();

        Booking::factory()->for($car)->between($from, $to)->create();

        $this->artisan('aparcado:close-bookings');

        // El historial no se reescribe: esas fechas siguen siendo de quien las pagó.
        $this->assertFalse(Car::query()->freeBetween($from, $to)->whereKey($car->id)->exists());
    }

    public function test_the_command_says_so_when_there_is_nothing_to_close(): void
    {
        $this->artisan('aparcado:close-bookings')
            ->expectsOutputToContain('No había ninguna que cerrar.')
            ->assertSuccessful();
    }

    /** @return array{from: string, to: string} */
    private function dates(string $from = '+5 days', string $to = '+8 days'): array
    {
        return [
            'from' => Carbon::parse($from)->toDateString(),
            'to' => Carbon::parse($to)->toDateString(),
        ];
    }
}
