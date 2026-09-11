<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    /**
     * Una conversación es de dos personas. Nadie más la lee, aunque tenga el
     * enlace: los mensajes llevan dónde se recoge un coche y a qué hora está uno
     * en su casa.
     */
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->includes($user);
    }

    public function send(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
