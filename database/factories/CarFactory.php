<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Car>
 */
class CarFactory extends Factory
{
    /** Marcas con modelos que existen de verdad, para que los datos de ejemplo no
     * hablen de un «Renault Panda». */
    private const MODELS = [
        'Seat' => ['Ibiza', 'León', 'Arona', 'Ateca'],
        'Renault' => ['Clio', 'Captur', 'Mégane', 'Kadjar'],
        'Peugeot' => ['208', '308', '2008', '3008'],
        'Volkswagen' => ['Polo', 'Golf', 'T-Roc', 'Tiguan'],
        'Toyota' => ['Yaris', 'Corolla', 'C-HR', 'RAV4'],
        'Dacia' => ['Sandero', 'Duster', 'Jogger'],
        'Fiat' => ['500', 'Panda', 'Tipo'],
        'Citroën' => ['C3', 'C4', 'Berlingo'],
    ];

    public function definition(): array
    {
        $brand = fake()->randomElement(array_keys(self::MODELS));

        return [
            'owner_id' => User::factory(),
            'plate' => $this->plate(),
            'brand' => $brand,
            'model' => fake()->randomElement(self::MODELS[$brand]),
            'registration_year' => fake()->numberBetween(2010, 2025),
            'kilometres' => fake()->numberBetween(5_000, 220_000),
            'fuel' => fake()->randomElement(config('aparcado.fuels')),
            'transmission' => fake()->randomElement(config('aparcado.transmissions')),
            'body_type' => fake()->randomElement(config('aparcado.body_types')),
            'colour' => fake()->randomElement(['Blanco', 'Negro', 'Gris', 'Azul', 'Rojo', 'Plata']),
            'seats' => fake()->randomElement([2, 4, 5, 5, 5, 7]),
            'doors' => fake()->randomElement([3, 5, 5]),
            'power_hp' => fake()->numberBetween(70, 200),
            'has_insurance' => fake()->boolean(80),
            'price_cents' => fake()->numberBetween(1800, 9500),
            'description' => fake('es_ES')->paragraph(),
            'address' => fake('es_ES')->streetAddress(),
            'city' => fake('es_ES')->city(),
            'province_code' => '29',
            'postal_code' => fake()->numerify('29###'),
            'country' => 'ES',
            'latitude' => fake()->latitude(36.6, 37.3),
            'longitude' => fake()->longitude(-5.6, -4.2),
            'parking_type' => fake()->randomElement(config('aparcado.parking_types')),
            'published' => true,
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => ['published' => false]);
    }

    /** Matrícula española de las de ahora: cuatro cifras y tres consonantes. */
    private function plate(): string
    {
        $letters = collect(str_split('BCDFGHJKLMNPRSTVWXYZ'))
            ->random(3)
            ->implode('');

        return fake()->unique()->numerify('####').' '.$letters;
    }
}
