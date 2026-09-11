<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class DateRange
{
    /**
     * «del 3 al 7 de octubre», y sólo «del 3 de octubre al 2 de noviembre» cuando
     * hace falta: repetir el mes dos veces en la misma frase se lee peor.
     */
    public static function forHumans(string $from, string $to): string
    {
        $start = Carbon::parse($from);
        $end = Carbon::parse($to);

        if ($start->isSameMonth($end)) {
            return 'del '.$start->day.' al '.$end->translatedFormat('j \d\e F');
        }

        return 'del '.$start->translatedFormat('j \d\e F').' al '.$end->translatedFormat('j \d\e F');
    }
}
