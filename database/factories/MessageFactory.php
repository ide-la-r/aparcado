<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            // Por defecto escribe el interesado, que es quien siempre empieza.
            'sender_id' => fn (array $attributes) => Conversation::query()
                ->whereKey($attributes['conversation_id'])
                ->value('renter_id') ?? User::factory(),
            'body' => fake('es_ES')->sentence(),
        ];
    }

    public function from(User $user): static
    {
        return $this->state(fn (array $attributes) => ['sender_id' => $user->id]);
    }
}
