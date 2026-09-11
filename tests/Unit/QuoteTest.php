<?php

namespace Tests\Unit;

use App\Models\Car;
use App\Services\Bookings\Quote;
use Tests\TestCase;

class QuoteTest extends TestCase
{
    public function test_the_day_you_give_the_car_back_counts(): void
    {
        $car = Car::factory()->make(['price_cents' => 3500]);

        $quote = Quote::for($car, '2026-10-05', '2026-10-05');

        $this->assertSame(1, $quote->days);
        $this->assertSame(3500, $quote->totalCents);
        $this->assertSame('1 día × 35 €', $quote->breakdownForHumans());
    }

    public function test_it_multiplies_the_days_by_the_price_of_the_car(): void
    {
        $car = Car::factory()->make(['price_cents' => 4250]);

        $quote = Quote::for($car, '2026-10-03', '2026-10-07');

        $this->assertSame(5, $quote->days);
        $this->assertSame(21250, $quote->totalCents);
        $this->assertSame('212,50 €', $quote->totalForHumans());
        $this->assertSame('5 días × 42,50 €', $quote->breakdownForHumans());
    }
}
