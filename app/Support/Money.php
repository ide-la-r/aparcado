<?php

namespace App\Support;

/**
 * Todo importe vive en céntimos y en un entero; esto es lo único que lo convierte
 * en texto. Está en un sitio para que no aparezca un `number_format` distinto en
 * cada vista y acabe habiendo precios con punto decimal en media aplicación.
 */
class Money
{
    public static function format(int $cents): string
    {
        return number_format($cents / 100, 2, ',', '.').' €';
    }

    /** Sin decimales cuando son céntimos redondos: «35 €» se lee mejor que «35,00 €». */
    public static function short(int $cents): string
    {
        return $cents % 100 === 0
            ? number_format(intdiv($cents, 100), 0, ',', '.').' €'
            : self::format($cents);
    }
}
