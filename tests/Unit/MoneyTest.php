<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_it_writes_amounts_the_spanish_way(): void
    {
        $this->assertSame('36,50 €', Money::format(3650));
        $this->assertSame('1.299,99 €', Money::format(129999));
        $this->assertSame('0,00 €', Money::format(0));
    }

    public function test_it_reads_what_somebody_types_in_a_form(): void
    {
        $this->assertSame(3550, Money::toCents('35,50'));
        // El teclado numérico de un móvil pone un punto, no una coma.
        $this->assertSame(3550, Money::toCents('35.50'));
        $this->assertSame(3500, Money::toCents('35'));
        $this->assertSame(3550, Money::toCents(' 35,50 € '));
        // Un céntimo y medio no existe.
        $this->assertSame(3551, Money::toCents('35,509'));
    }

    public function test_what_is_not_a_number_is_not_an_amount(): void
    {
        $this->assertNull(Money::toCents('lo que sea'));
        $this->assertNull(Money::toCents(''));
        $this->assertNull(Money::toCents(null));
        $this->assertNull(Money::toCents([]));
    }

    public function test_round_amounts_lose_the_decimals_in_the_short_form(): void
    {
        $this->assertSame('35 €', Money::short(3500));
        $this->assertSame('1.200 €', Money::short(120000));
        $this->assertSame('9,99 €', Money::short(999));
    }
}
