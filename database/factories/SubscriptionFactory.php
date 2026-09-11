<?php

namespace Database\Factories;

use App\Enums\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            ...$this->pricing(Plan::Plus),
            'starts_on' => Carbon::today(),
            'ends_on' => Carbon::today()->addMonth(),
        ];
    }

    public function plan(Plan $plan): static
    {
        // Sólo el plan y su precio: un estado que devolviera `user_id` pisaría el
        // `for($user)` de quien llama y la suscripción acabaría en otra persona.
        return $this->state(fn (array $attributes) => $this->pricing($plan));
    }

    /** Una que ya caducó: sirve para comprobar que deja de contar. */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_on' => Carbon::today()->subMonths(2),
            'ends_on' => Carbon::today()->subMonth(),
        ]);
    }

    private function pricing(Plan $plan): array
    {
        return [
            'plan' => $plan,
            // El precio se copia del plan al contratarlo: si mañana sube, lo que
            // esta persona pagó no cambia.
            'price_cents' => $plan->priceCents(),
        ];
    }
}
