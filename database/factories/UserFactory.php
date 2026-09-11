<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /** La contraseña se cifra una sola vez: bcrypt por cada usuario de prueba
     * convierte un seeder de dos segundos en uno de treinta. */
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake('es_ES')->firstName(),
            'surname' => fake('es_ES')->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => fake('es_ES')->numerify('6########'),
            'birthdate' => fake()->dateTimeBetween('-60 years', '-21 years')->format('Y-m-d'),
            'document_type' => 'DNI',
            'document_number' => fake()->unique()->numerify('########').fake()->randomLetter(),
            'verified_at' => now(),
            'active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /** Registrado, pero sin que nadie le haya mirado los papeles todavía. */
    public function pendingReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => null,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
