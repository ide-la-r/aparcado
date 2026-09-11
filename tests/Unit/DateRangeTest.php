<?php

namespace Tests\Unit;

use App\Support\DateRange;
use Tests\TestCase;

class DateRangeTest extends TestCase
{
    public function test_it_says_the_month_once_when_both_days_share_it(): void
    {
        $this->assertSame('del 3 al 7 de octubre', DateRange::forHumans('2026-10-03', '2026-10-07'));
    }

    public function test_it_says_both_months_when_the_range_crosses_one(): void
    {
        $this->assertSame(
            'del 28 de octubre al 2 de noviembre',
            DateRange::forHumans('2026-10-28', '2026-11-02'),
        );
    }

    public function test_the_same_day_in_two_different_years_is_not_the_same_month(): void
    {
        $this->assertSame(
            'del 3 de octubre al 3 de octubre',
            DateRange::forHumans('2026-10-03', '2027-10-03'),
        );
    }
}
