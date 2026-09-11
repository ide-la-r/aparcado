<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /** La reserva la ven las dos partes, y nadie más. */
    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->renter_id
            || $user->id === $booking->car->owner_id;
    }

    /**
     * Cancelar lo puede hacer cualquiera de los dos: quien alquila porque le ha
     * cambiado el plan, y el dueño porque el coche puede estar en el taller. Lo que
     * no se puede es cancelar lo de otra gente.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        return $this->view($user, $booking);
    }
}
