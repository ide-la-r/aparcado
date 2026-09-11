<?php

namespace App\Enums;

enum BookingStatus: string
{
    /** Pedida, pendiente de pago. No bloquea el coche todavÃ­a. */
    case Pending = 'pending';

    /** Pagada. Ã‰sta es la que ocupa las fechas. */
    case Confirmed = 'confirmed';

    case Cancelled = 'cancelled';

    /** Terminada: la fecha de fin ya pasÃ³. */
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente de pago',
            self::Confirmed => 'Confirmada',
            self::Cancelled => 'Cancelada',
            self::Completed => 'Terminada',
        };
    }

    /**
     * Los estados que de verdad ocupan el coche. Una reserva sin pagar no puede
     * dejar un coche fuera del catÃ¡logo: era la forma de tumbar la competencia en
     * el TFG, donde cualquier reserva bloqueaba las fechas.
     */
    public static function blocking(): array
    {
        return [self::Confirmed, self::Completed];
    }
}
