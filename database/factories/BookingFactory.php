<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Car;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        $starts = Carbon::parse(fake()->dateTimeBetween('-2 months', '+2 months'))->startOfDay();
        $days = fake()->numberBetween(1, 10);
        $perDay = fake()->numberBetween(1800, 9500);

        return [
            'car_id' => Car::factory(),
            'renter_id' => User::factory(),
            'starts_on' => $starts,
            // El día de fin cuenta, así que una reserva de un día empieza y acaba
            // el mismo día.
            'ends_on' => $starts->copy()->addDays($days - 1),
            'days' => $days,
            'price_cents_per_day' => $perDay,
            'total_cents' => $perDay * $days,
            'status' => BookingStatus::Confirmed,
            'confirmed_at' => now(),
        ];
    }

    /** Pedida y sin pagar: no bloquea las fechas del coche. */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::Pending,
            'confirmed_at' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }

    public function between(string $from, string $to): static
    {
        $starts = Carbon::parse($from)->startOfDay();
        $ends = Carbon::parse($to)->startOfDay();

        return $this->state(fn (array $attributes) => [
            'starts_on' => $starts,
            'ends_on' => $ends,
            'days' => $starts->diffInDays($ends) + 1,
        ]);
    }
}
