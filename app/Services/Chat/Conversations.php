<?php

namespace App\Services\Chat;

use App\Models\Car;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

class Conversations
{
    /**
     * La conversación de este coche con esta persona, creándola si es la primera
     * vez. Una sola por coche e interesado: escribir otra vez continúa la que ya
     * había, en lugar de abrir un hilo nuevo cada vez que a alguien le da por
     * preguntar.
     */
    public function openFor(Car $car, User $renter): Conversation
    {
        $existing = Conversation::query()
            ->where('car_id', $car->id)
            ->where('renter_id', $renter->id)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        /*
         * Montada a mano y no con `firstOrCreate`: quién habla con quién lo decide
         * el servidor, así que esas tres columnas se quedan fuera de la asignación
         * en masa —y `firstOrCreate` es asignación en masa—.
         */
        $conversation = new Conversation;
        $conversation->car()->associate($car);
        $conversation->owner()->associate($car->owner_id);
        $conversation->renter()->associate($renter);
        $conversation->save();

        return $conversation;
    }

    public function send(Conversation $conversation, User $sender, string $body): Message
    {
        $message = new Message(['body' => $body]);
        $message->conversation()->associate($conversation);
        $message->sender()->associate($sender);
        $message->save();

        // La bandeja se ordena por esto, así que no hay que contar mensajes en cada
        // carga para saber qué conversación va arriba.
        $conversation->forceFill(['last_message_at' => $message->created_at])->save();

        return $message;
    }

    /**
     * Marca como leídos los mensajes que ha escrito la otra persona. Los propios no
     * se tocan: «leído» significa que lo ha visto quien lo recibió.
     */
    public function markAsRead(Conversation $conversation, User $reader): void
    {
        $conversation->messages()
            ->where('sender_id', '!=', $reader->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
