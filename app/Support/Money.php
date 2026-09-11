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

    /**
     * Y el camino de vuelta: lo que alguien escribe en un formulario, a céntimos.
     *
     * Aquí se escribe «35,50» con coma, pero el teclado numérico de un móvil pone
     * un punto, así que hay que aceptar los dos. Se redondea porque «35,509»
     * existe y un céntimo y medio no. Devuelve null cuando eso no es un número, y
     * es quien llama el que decide si eso es un error o un campo vacío.
     */
    public static function toCents(mixed $amount): ?int
    {
        if (! is_string($amount) && ! is_numeric($amount)) {
            return null;
        }

        $clean = str_replace([' ', '€', ','], ['', '', '.'], (string) $amount);

        return is_numeric($clean) ? (int) round(((float) $clean) * 100) : null;
    }
}
