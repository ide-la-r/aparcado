<?php

namespace App\Enums;

/** Cómo ha acabado un intento de cobrar. */
enum CaptureOutcome: string
{
    case Captured = 'captured';

    /** Ya estaba cobrada: el navegador mandó la vuelta dos veces. */
    case AlreadyCaptured = 'already_captured';

    /** La pasarela cobró algo distinto de lo que se pidió. */
    case AmountMismatch = 'amount_mismatch';

    /** No se pudo hablar con la pasarela. */
    case GatewayError = 'gateway_error';

    /** Alguien pagó esas fechas antes, y el dinero se ha devuelto. */
    case DatesTakenAndRefunded = 'dates_taken_refunded';

    /** Lo mismo, pero la devolución falló y hay que hacerla a mano. */
    case DatesTakenRefundFailed = 'dates_taken_refund_failed';

    public function message(): string
    {
        return match ($this) {
            self::Captured => 'Pagado. El coche es tuyo esos días.',
            self::AlreadyCaptured => 'Esta reserva ya estaba pagada.',
            self::AmountMismatch => 'El importe cobrado no coincide con el de la reserva. No hemos confirmado nada; escríbenos y lo arreglamos.',
            self::GatewayError => 'No hemos podido hablar con PayPal. No se te ha cobrado nada; inténtalo otra vez.',
            self::DatesTakenAndRefunded => 'Alguien pagó esas fechas antes que tú, así que te hemos devuelto el dinero.',
            self::DatesTakenRefundFailed => 'Alguien pagó esas fechas antes que tú. La devolución no ha salido automáticamente: escríbenos y la hacemos a mano.',
        };
    }

    public function isGood(): bool
    {
        return in_array($this, [self::Captured, self::AlreadyCaptured], true);
    }
}
