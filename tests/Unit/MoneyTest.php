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

    public function test_round_amounts_lose_the_decimals_in_the_short_form(): void
    {
        $this->assertSame('35 €', Money::short(3500));
        $this->assertSame('1.200 €', Money::short(120000));
        $this->assertSame('9,99 €', Money::short(999));
    }
}
