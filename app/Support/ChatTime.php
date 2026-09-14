<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Cómo se escriben las horas del chat.
 *
 * Vive aparte porque lo usan dos sitios: la vista al pintar la conversación y la
 * ruta que devuelve lo que va llegando. Si cada uno diera su formato, el mensaje
 * recién llegado se vería distinto del de justo encima.
 */
class ChatTime
{
    /**
     * La etiqueta del día que separa los mensajes.
     *
     * Sólo con la hora, un mensaje de la semana pasada se lee como de hace un
     * rato: «11:30» no dice nada por sí solo.
     */
    public static function day(CarbonInterface $at): string
    {
        if ($at->isToday()) {
            return 'Hoy';
        }

        if ($at->isYesterday()) {
            return 'Ayer';
        }

        // El año sólo cuando no es este: ponerlo siempre es ruido.
        return $at->isCurrentYear()
            ? $at->translatedFormat('j \d\e F')
            : $at->translatedFormat('j \d\e F \d\e Y');
    }

    public static function hour(CarbonInterface $at): string
    {
        return $at->format('H:i');
    }
}
