<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'car_id' => Car::factory(),
            // Perezoso a propósito: si quien llama ya pasa un coche, el dueño sale
            // de ese coche y no se crea ninguno de más. Con un `Car::factory()`
            // suelto aquí, cada conversación dejaba un coche huérfano detrás.
            'owner_id' => fn (array $attributes) => Car::query()
                ->whereKey($attributes['car_id'])
                ->value('owner_id') ?? User::factory(),
            'renter_id' => User::factory(),
        ];
    }
}
