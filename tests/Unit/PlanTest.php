<?php

namespace Tests\Unit;

use App\Enums\Plan;
use Tests\TestCase;

class PlanTest extends TestCase
{
    public function test_the_prices_are_the_ones_of_the_original_project(): void
    {
        $this->assertSame(1999, Plan::Premium->priceCents());
        $this->assertSame(999, Plan::Plus->priceCents());
    }

    public function test_premium_goes_before_plus_and_plus_before_nobody(): void
    {
        $this->assertGreaterThan(Plan::Plus->priority(), Plan::Premium->priority());
        $this->assertGreaterThan(0, Plan::Plus->priority());
    }

    public function test_every_plan_has_a_name_a_price_and_a_place_in_the_catalogue(): void
    {
        foreach (Plan::cases() as $plan) {
            $this->assertNotSame('', $plan->label());
            $this->assertGreaterThan(0, $plan->priceCents());
        }
    }

    public function test_the_configured_plans_and_the_enum_say_the_same(): void
    {
        // Un plan en la configuración que no exista en el enum es un plan que se
        // puede cobrar y no se puede leer.
        $this->assertEqualsCanonicalizing(
            array_keys(config('aparcado.plans')),
            array_column(Plan::cases(), 'value'),
        );
    }
}
