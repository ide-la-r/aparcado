<?php

namespace App\Enums;

enum PaymentStatus: string
{
    /** La orden está creada en la pasarela, nadie ha pagado todavía. */
    case Created = 'created';

    /** Cobrado y comprobado. */
    case Captured = 'captured';

    /** La pasarela dijo que no, o el importe no cuadraba. */
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Empezado',
            self::Captured => 'Cobrado',
            self::Failed => 'Fallido',
        };
    }
}
